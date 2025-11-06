<?php
/**
 * Setting Model
 */
class Setting {
    /**
     * Get all settings
     */
    public static function all() {
        $rows = DB::query('SELECT k, v FROM settings ORDER BY k');
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['k']] = $row['v'];
        }

        return $settings;
    }

    /**
     * Get setting value
     */
    public static function get($key, $default = null) {
        return setting($key, $default);
    }

    /**
     * Update setting
     */
    public static function update($key, $value) {
        return update_setting($key, $value);
    }

    /**
     * Update multiple settings
     */
    public static function updateMany($settings) {
        DB::beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                self::update($key, $value);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get theme settings
     */
    public static function getTheme() {
        $theme_name = self::get('theme', 'default');

        return DB::queryOne('SELECT * FROM themes WHERE name = ?', [$theme_name]);
    }

    /**
     * Get all themes
     */
    public static function getThemes() {
        return DB::query('SELECT * FROM themes WHERE is_active = 1 ORDER BY name');
    }
}
