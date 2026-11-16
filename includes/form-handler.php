<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/phone-country-data.php';

function start_form_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $sessionPath = session_save_path();
    if ($sessionPath === '' || !is_dir($sessionPath) || !is_writable($sessionPath)) {
        $fallbackSessionPath = sys_get_temp_dir();
        if (!is_dir($fallbackSessionPath) || !is_writable($fallbackSessionPath)) {
            throw new RuntimeException('No writable session storage is available.');
        }
        session_save_path($fallbackSessionPath);
    }

    $isSecure = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    if (!$isSecure && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $forwardedProtocols = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO']);
        $isSecure = strtolower(trim($forwardedProtocols[0])) === 'https';
    }
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isSecure,
        'samesite' => 'Lax',
    ]);

    if (!session_start()) {
        throw new RuntimeException('Unable to start a session for form protection.');
    }
}

function create_form_state(): array
{
    start_form_session();

    if (!isset($_SESSION['form_csrf_token'])) {
        $_SESSION['form_csrf_token'] = bin2hex(random_bytes(32));
    }

    $state = [
        'csrf_token' => $_SESSION['form_csrf_token'],
        'errors' => [],
        'values' => [],
        'success' => $_SESSION['form_success'] ?? '',
    ];
    unset($_SESSION['form_success']);

    return $state;
}

function read_form_value(array $input, string $key): string
{
    $value = $input[$key] ?? '';
    if (!is_string($value) && !is_numeric($value)) {
        return '';
    }

    return trim((string) $value);
}

function form_has_invalid_field_types(array $input, array $fields): bool
{
    foreach ($fields as $field) {
        if (array_key_exists($field, $input)
            && !is_string($input[$field])
            && !is_numeric($input[$field])) {
            return true;
        }
    }

    return false;
}

function normalize_phone_number(string $phone, string $countryIso): ?string
{
    $countries = phone_country_options();
    $country = $countries[$countryIso] ?? null;
    if (!is_array($country) || !preg_match('/^[0-9\s().-]+$/D', $phone)) {
        return null;
    }

    $nationalNumber = preg_replace('/[\s().-]/', '', $phone);
    if (!is_string($nationalNumber) || $nationalNumber === '') {
        return null;
    }

    $numberLength = strlen($nationalNumber);
    $dialCodeLength = strlen(preg_replace('/\D/', '', $country['dial_code']));
    $maximumLength = min($country['max_digits'], 15 - $dialCodeLength);
    if ($numberLength < $country['min_digits']
        || $numberLength > $maximumLength
        || preg_match('/^[0-9]+$/D', $nationalNumber) !== 1
        || (!empty($country['leading_digits']) && strpos($country['leading_digits'], $nationalNumber[0]) === false)) {
        return null;
    }

    return $country['dial_code'] . $nationalNumber;
}

function form_values_exceed_limits(array $values, array $limits): bool
{
    foreach ($limits as $key => $maxLength) {
        $value = $values[$key] ?? '';
        if (preg_match('//u', $value) !== 1) {
            return true;
        }

        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > $maxLength) {
            return true;
        }
    }

    return false;
}

function validate_form_csrf(array $input): bool
{
    $submittedToken = $input['csrf_token'] ?? '';
    return is_string($submittedToken)
        && isset($_SESSION['form_csrf_token'])
        && hash_equals($_SESSION['form_csrf_token'], $submittedToken);
}

function send_form_notification(string $type, array $values): bool
{
    $recipient = getenv('ENERGYHUB_NOTIFICATION_EMAIL') ?: 'gravityhubautomation@gmail.com';
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        error_log('EnergyHub form notification failed: invalid recipient email configuration.');
        return false;
    }

    if ($type === 'contact') {
        $subject = 'New EnergyHub contact message';
        $replyTo = $values['email'];
        $fields = [
            'Name' => $values['name'],
            'Email' => $values['email'],
            'Phone' => $values['phone'],
            'Subject' => $values['subject'],
            'Message' => $values['message'],
        ];
    } else {
        $subject = 'New EnergyHub service request';
        $replyTo = $values['customerEmail'];
        $fields = [
            'Company or customer' => $values['customerName'],
            'Phone' => $values['customerPhone'],
            'Email' => $values['customerEmail'],
            'Service type' => $values['serviceType'],
            'Equipment' => $values['equipmentType'],
            'Manufacturer and model' => $values['deviceModel'],
            'Site location' => $values['siteLocation'],
            'Materials needed' => $values['materialNeeds'],
            'Request details' => $values['issueDescription'],
            'Preferred contact method' => $values['contactMethod'],
            'Preferred time' => $values['preferredTime'],
        ];
    }

    $body = "A new submission was received from the EnergyHub website.\n\n";
    foreach ($fields as $label => $value) {
        $body .= $label . ":\n" . ($value !== '' ? $value : '(not provided)') . "\n\n";
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $replyTo,
    ];
    $sender = getenv('ENERGYHUB_MAIL_FROM');
    if ($sender !== false && $sender !== '') {
        if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            error_log('EnergyHub form notification failed: invalid sender email configuration.');
            return false;
        }
        $headers[] = 'From: EnergyHub Website <' . $sender . '>';
    }

    if (!mail($recipient, $subject, $body, implode("\r\n", $headers))) {
        error_log('EnergyHub form notification could not be handed to the configured mail transport.');
        return false;
    }

    return true;
}

function form_submission_result(string $type, array $input, array $serviceIds): array
{
    start_form_session();

    $state = [
        'csrf_token' => $_SESSION['form_csrf_token'] ?? '',
        'errors' => [],
        'values' => [],
        'success' => '',
    ];

    if (!validate_form_csrf($input)) {
        $state['errors']['general'] = 'Your form session expired. Reload the page and submit it again.';
        return $state;
    }

    if ($type === 'contact') {
        if (form_has_invalid_field_types($input, [
            'name', 'email', 'phone', 'phoneCountryCode', 'subject', 'message',
        ])) {
            $state['errors']['general'] = 'One or more fields have an invalid format. Please review your entries.';
            return $state;
        }

        $state['values'] = [
            'name' => read_form_value($input, 'name'),
            'email' => read_form_value($input, 'email'),
            'phone' => read_form_value($input, 'phone'),
            'phoneCountryCode' => read_form_value($input, 'phoneCountryCode'),
            'subject' => read_form_value($input, 'subject'),
            'message' => read_form_value($input, 'message'),
        ];

        if ($state['values']['name'] === '') {
            $state['errors']['name'] = 'Please enter your name.';
        }
        if (!filter_var($state['values']['email'], FILTER_VALIDATE_EMAIL)) {
            $state['errors']['email'] = 'Please enter a valid email address.';
        }
        if (!isset(phone_country_options()[$state['values']['phoneCountryCode']])) {
            $state['errors']['phoneCountryCode'] = 'Please select a valid country code.';
        }
        if ($state['values']['phone'] !== ''
            && normalize_phone_number($state['values']['phone'], $state['values']['phoneCountryCode']) === null) {
            $state['errors']['phone'] = 'Enter a valid phone number for the selected country.';
        }
        if ($state['values']['message'] === '') {
            $state['errors']['message'] = 'Please describe your request.';
        }
    } elseif ($type === 'service_request') {
        if (form_has_invalid_field_types($input, [
            'customerName',
            'customerPhone',
            'customerPhoneCountryCode',
            'customerEmail',
            'serviceType',
            'equipmentType',
            'deviceModel',
            'siteLocation',
            'materialNeeds',
            'issueDescription',
            'contactMethod',
            'preferredTime',
        ])) {
            $state['errors']['general'] = 'One or more fields have an invalid format. Please review your entries.';
            return $state;
        }

        $state['values'] = [
            'customerName' => read_form_value($input, 'customerName'),
            'customerPhone' => read_form_value($input, 'customerPhone'),
            'customerPhoneCountryCode' => read_form_value($input, 'customerPhoneCountryCode'),
            'customerEmail' => read_form_value($input, 'customerEmail'),
            'serviceType' => read_form_value($input, 'serviceType'),
            'equipmentType' => read_form_value($input, 'equipmentType'),
            'deviceModel' => read_form_value($input, 'deviceModel'),
            'siteLocation' => read_form_value($input, 'siteLocation'),
            'materialNeeds' => read_form_value($input, 'materialNeeds'),
            'issueDescription' => read_form_value($input, 'issueDescription'),
            'contactMethod' => read_form_value($input, 'contactMethod'),
            'preferredTime' => read_form_value($input, 'preferredTime'),
        ];

        if ($state['values']['customerName'] === '') {
            $state['errors']['customerName'] = 'Please enter your company or name.';
        }
        if (!isset(phone_country_options()[$state['values']['customerPhoneCountryCode']])) {
            $state['errors']['customerPhoneCountryCode'] = 'Please select a valid country code.';
        }
        if (normalize_phone_number(
            $state['values']['customerPhone'],
            $state['values']['customerPhoneCountryCode']
        ) === null) {
            $state['errors']['customerPhone'] = 'Enter a valid phone number for the selected country.';
        }
        if (!filter_var($state['values']['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            $state['errors']['customerEmail'] = 'Please enter a valid email address.';
        }
        if (!in_array($state['values']['serviceType'], $serviceIds, true)) {
            $state['errors']['serviceType'] = 'Please select a valid service.';
        }
        if (!in_array($state['values']['materialNeeds'], ['', 'yes', 'no', 'unsure'], true)) {
            $state['errors']['materialNeeds'] = 'Please select a valid option.';
        }
        if ($state['values']['issueDescription'] === '') {
            $state['errors']['issueDescription'] = 'Please describe the fault or required task.';
        }
        if (!in_array($state['values']['contactMethod'], ['phone', 'email'], true)) {
            $state['errors']['contactMethod'] = 'Please select a valid contact method.';
        }
    } else {
        throw new InvalidArgumentException('Unsupported form type.');
    }

    $maxLengths = $type === 'contact'
        ? [
            'name' => 150,
            'email' => 254,
            'phone' => 18,
            'phoneCountryCode' => 2,
            'subject' => 200,
            'message' => 5000,
        ]
        : [
            'customerName' => 150,
            'customerPhone' => 18,
            'customerPhoneCountryCode' => 2,
            'customerEmail' => 254,
            'serviceType' => 60,
            'equipmentType' => 200,
            'deviceModel' => 200,
            'siteLocation' => 255,
            'materialNeeds' => 10,
            'issueDescription' => 5000,
            'contactMethod' => 20,
            'preferredTime' => 150,
        ];
    if (form_values_exceed_limits($state['values'], $maxLengths)) {
        $state['errors']['general'] = 'One or more fields are too long or contain invalid text. Please review your entries.';
    }

    if ($state['errors']) {
        return $state;
    }

    $phoneField = $type === 'contact' ? 'phone' : 'customerPhone';
    $phoneCountryField = $type === 'contact' ? 'phoneCountryCode' : 'customerPhoneCountryCode';
    $normalizedPhone = $state['values'][$phoneField] !== ''
        ? normalize_phone_number($state['values'][$phoneField], $state['values'][$phoneCountryField])
        : null;

    try {
        $connection = database_connection();
        if ($type === 'contact') {
            $statement = $connection->prepare(
                'INSERT INTO contact_messages (name, email, phone, subject, message)
                 VALUES (:name, :email, :phone, :subject, :message)'
            );
            $statement->execute([
                'name' => $state['values']['name'],
                'email' => $state['values']['email'],
                'phone' => $normalizedPhone,
                'subject' => $state['values']['subject'] ?: null,
                'message' => $state['values']['message'],
            ]);
        } else {
            $statement = $connection->prepare(
                'INSERT INTO service_requests
                    (customer_name, phone, email, service_type, equipment_type, device_model,
                     site_location, material_needs, issue_description, contact_method, preferred_time)
                 VALUES
                    (:customer_name, :phone, :email, :service_type, :equipment_type, :device_model,
                     :site_location, :material_needs, :issue_description, :contact_method, :preferred_time)'
            );
            $statement->execute([
                'customer_name' => $state['values']['customerName'],
                'phone' => $normalizedPhone,
                'email' => $state['values']['customerEmail'],
                'service_type' => $state['values']['serviceType'],
                'equipment_type' => $state['values']['equipmentType'] ?: null,
                'device_model' => $state['values']['deviceModel'] ?: null,
                'site_location' => $state['values']['siteLocation'] ?: null,
                'material_needs' => $state['values']['materialNeeds'] ?: null,
                'issue_description' => $state['values']['issueDescription'],
                'contact_method' => $state['values']['contactMethod'],
                'preferred_time' => $state['values']['preferredTime'] ?: null,
            ]);
        }
    } catch (PDOException | RuntimeException $exception) {
        error_log('EnergyHub form database save failed: ' . $exception->getMessage());
        $state['errors']['general'] = 'We could not save your submission right now. Please try again later.';
        return $state;
    }

    $state['saved'] = true;
    $notificationValues = $state['values'];
    $notificationValues[$phoneField] = $normalizedPhone ?? '';
    $state['notification_sent'] = send_form_notification($type, $notificationValues);
    return $state;
}

function escape_form_value(array $state, string $key): string
{
    return htmlspecialchars($state['values'][$key] ?? '', ENT_QUOTES, 'UTF-8');
}

function form_field_error(array $state, string $key, string $fallback): string
{
    return htmlspecialchars($state['errors'][$key] ?? $fallback, ENT_QUOTES, 'UTF-8');
}
