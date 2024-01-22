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
 * Allows plugins to extend course form.
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
    public $module;

    /**
     * Instance of activity.
     *
     * @var object
     */
    public $activity;

    /**
     * Course module id.
     *
     * @var int
     */
    public $cmid;

    /**
     * Intro.
     *
     * @var string
     */
    public $intro;

    /**
     * Intro format.
     *
     * @var int
     */

    /**
     * Formatting options.
     *
     * @var array
     */
    public $options;
    public $introformat;

    /**
     * Creates new hook.
     *
     * @param string $intro Intro.
     * @param int $format Intro format.
     */
    public function __construct(string $module, object $activity, int $cmid, string $intro, int $introformat, array $options) {
        $this->module = $module;
        $this->activity = $activity;
        $this->cmid = $cmid;
        $this->intro = $intro;
        $this->introformat = $introformat;
        $this->options = $options;
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
}
