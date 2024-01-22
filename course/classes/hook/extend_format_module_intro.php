<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core_course\hook;

use core\hook\described_hook;

/**
 * Allows plugins to extend a module description.
 *
 * @package    core
 * @copyright  2024 Catalyst IT Australia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class extend_format_module_intro implements described_hook {
    /**
     * Name of module.
     *
     * @var string
     */
    private $module;

    /**
     * Instance of activity.
     *
     * @var object
     */
    private $activity;

    /**
     * Course module id.
     *
     * @var int
     */
    private $cmid;

    /**
     * Intro.
     *
     * @var string
     */
    private $intro;

    /**
     * Creates new hook.
     *
     * @param string $module
     * @param object $activity
     * @param int $cmid
     * @param string $intro
     */
    public function __construct(string $module, object $activity, int $cmid, string $intro) {
        $this->module = $module;
        $this->activity = $activity;
        $this->cmid = $cmid;
        $this->intro = $intro;
    }

    /**
     * Describes the hook purpose.
     *
     * @return string
     */
    public static function get_hook_description(): string {
        return 'Allows plugins to extend a module description';
    }

    /**
     * List of tags that describe this hook.
     *
     * @return string[]
     */
    public static function get_hook_tags(): array {
        return ['course'];
    }

    /**
     * Get the value of module.
     *
     * @return string
     */
    public function get_module(): string {
        return $this->module;
    }

    /**
     * Get the value of activity.
     *
     * @return object
     */
    public function get_activity(): object {
        return $this->activity;
    }

    /**
     * Get the value of cmid.
     *
     * @return int
     */
    public function get_cmid(): int {
        return $this->cmid;
    }

    /**
     * Get the value of intro.
     *
     * @return string
     */
    public function get_intro(): string {
        return $this->intro;
    }

    /**
     * Set the value of intro.
     *
     * @param string $intro
     */
    public function set_intro(string $intro) {
        $this->intro = $intro;
    }
}
