<?php
/**
 * License Management
 *
 * Handles offline license generation and validation using HMAC
 */
class License {
    /**
     * Get school name from settings
     *
     * @return string School name
     */
    public static function school() {
        return setting('school_name', '');
    }

    /**
     * Get license data (key and expiration date)
     *
     * @return array License data
     */
    public static function get() {
        $key = setting('license_key', '');
        $until = setting('license_until', '');

        return [
            'license_key' => $key,
            'license_until' => $until
        ];
    }

    /**
     * Set license key and expiration date
     *
     * @param string $key License key
     * @param string $until Expiration date (Y-m-d)
     * @return bool Success status
     */
    public static function set($key, $until) {
        try {
            update_setting('license_key', $key);
            update_setting('license_until', $until);

            log_activity('license_updated', 'setting', null, 'License key updated until ' . $until);

            return true;
        } catch (Exception $e) {
            error_log('License update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate current license
     *
     * @return bool True if license is valid
     */
    public static function valid() {
        $data = self::get();
        $key = $data['license_key'];
        $until = $data['license_until'];

        // No license set
        if (empty($key) || empty($until)) {
            return false;
        }

        // Check expiration date
        try {
            $now = new DateTime('now');
            $expiration = new DateTime($until);

            if ($now > $expiration) {
                return false;
            }
        } catch (Exception $e) {
            error_log('License date parsing error: ' . $e->getMessage());
            return false;
        }

        // Validate HMAC
        $year = (new DateTime($until))->format('Y');
        $expected = self::make(self::school(), $year, APP_SECRET);

        return hash_equals($expected, $key);
    }

    /**
     * Generate license key
     *
     * Format: base64url(year + '.' + hex(hmac(school|year, secret)))
     *
     * @param string $school School name
     * @param string $year License year
     * @param string $secret Application secret
     * @return string License key
     */
    public static function make($school, $year, $secret) {
        $message = $school . '|' . $year;
        $mac = hash_hmac('sha256', $message, $secret, true);
        $key = rtrim(strtr(base64_encode($year . '.' . bin2hex($mac)), '+/', '-_'), '=');

        return $key;
    }

    /**
     * Get license status information
     *
     * @return array License status details
     */
    public static function status() {
        $data = self::get();
        $valid = self::valid();

        $status = [
            'valid' => $valid,
            'license_key' => $data['license_key'],
            'license_until' => $data['license_until'],
            'school_name' => self::school(),
            'days_remaining' => null,
            'expired' => false,
            'message' => ''
        ];

        if (empty($data['license_key']) || empty($data['license_until'])) {
            $status['message'] = 'Aucune licence n\'est configurée.';
            return $status;
        }

        try {
            $now = new DateTime('now');
            $expiration = new DateTime($data['license_until']);
            $diff = $now->diff($expiration);

            if ($now > $expiration) {
                $status['expired'] = true;
                $status['message'] = 'Votre licence a expiré le ' . $expiration->format('d/m/Y') . '.';
            } else {
                $status['days_remaining'] = $diff->days;

                if ($diff->days <= 30) {
                    $status['message'] = 'Votre licence expire dans ' . $diff->days . ' jour(s).';
                } else {
                    $status['message'] = 'Licence valide jusqu\'au ' . $expiration->format('d/m/Y') . '.';
                }
            }
        } catch (Exception $e) {
            $status['message'] = 'Erreur de validation de la licence.';
        }

        return $status;
    }

    /**
     * Check if license is expiring soon (within 30 days)
     *
     * @return bool True if expiring soon
     */
    public static function expiringSoon() {
        $status = self::status();
        return $status['valid'] &&
               $status['days_remaining'] !== null &&
               $status['days_remaining'] <= 30;
    }

    /**
     * Require valid license (redirect to license page if invalid)
     */
    public static function requireValid() {
        if (!self::valid()) {
            $current_page = current_page();

            // Allow access to login and license pages
            if (!in_array($current_page, ['login', 'logout', 'settings.license'])) {
                flash('error', 'Votre licence est invalide ou a expiré. Veuillez la renouveler.');
                redirect('settings.license');
            }
        }
    }
}
