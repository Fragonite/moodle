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
/**
 * Contains the service_factory, a locator for services for the exemptions subsystem.
 *
 * Services encapsulate the business logic, and any data manipulation code, and are what clients should interact with.
 *
 * @package   core_exemptions
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_exemptions;

defined('MOODLE_INTERNAL') || die();

/**
 * Class service_factory, providing functions for location of service objects for the exemptions subsystem.
 *
 * This class is responsible for providing service objects to clients only.
 *
 * @copyright 2018 Jake Dallimore <jrhdallimore@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_factory {

    /**
     * Returns a basic service object providing operations for user exemptions.
     *
     * @param \context_user $context the context of the user to which the service should be scoped.
     * @return \core_exemptions\local\service\user_exemption_service the service object.
     */
    // public static function get_service_for_user_context(\context_user $context): local\service\user_exemption_service {
    //     return new local\service\user_exemption_service($context, new local\repository\exemption_repository());
    // }

    /**
     * Returns a basic service object providing operations for exemptions belonging to a given component.
     *
     * @param string $component frankenstyle component name.
     * @return local\service\component_exemption_service the service object.
     */
    public static function get_service_for_component(string $component): local\service\component_exemption_service {
        return new local\service\component_exemption_service($component, new local\repository\exemption_repository());
    }
}

