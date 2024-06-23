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
 * Handles edit penalty form.
 *
 * @module     gradepenalty_duedate/edit_penalty_form
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Str from 'core/str';
import * as notification from 'core/notification';

/**
 * Store group label string
 */
let groupLabel = '';

/**
 * Selectors
 */
const SELECTORS = {
    GROUPS: '[data-groupname]',
    DELETE_BUTTON: '.deleterulebuttons',
    DELETED_RULES: '[name="deletedrules"]',
    groupWithNumber: number => `[data-groupname="rulegroup[${number}]"]`,
    groupLabel: groupid => `[id="${groupid}_label"]`,
};

/**
 * Update the group label with rule number.
 *
 */
const updateGroupLabel = () => {
    // Get all the group elements.
    let groups = document.querySelectorAll(SELECTORS.GROUPS);

    // Update the group label with rule number.
    let ruleNumber = 1;
    groups.forEach((group) => {
        // Skip if the group is hidden.
        if (group.style.display === 'none') {
            return;
        }
        let label = document.querySelector(SELECTORS.groupLabel(group.id));
        if (label) {
            label.textContent = groupLabel + " " + (ruleNumber++);
        }
    });
};

/**
 * Register click event for delete buttons.
 */
const registerEventListeners = () => {
    // Find all delete buttons and add event listeners.
    const deleteButtons = document.querySelectorAll(SELECTORS.DELETE_BUTTON);
    deleteButtons.forEach(button => {
        button.addEventListener('click', e => {
            e.preventDefault();
            deleteRule(e.target);
        });
    });
};

/**
 * Delete a rule group.
 *
 * @param {Object} target
 */
const deleteRule = target => {
    // Extract the rule number from element name.
    let name = target.getAttribute('name');
    let rulenumber = name.match(/\d+/)[0];

    // Find the group element having the rule number.
    let group = document.querySelector(SELECTORS.groupWithNumber(rulenumber));

    // Hide the group element.
    group.style.display = 'none';

    // Update the group label.
    updateGroupLabel();

    // Add the rule number to the deleted rules input.
    let deletedRules = document.querySelector(SELECTORS.DELETED_RULES);
    deletedRules.value += rulenumber + ',';
};

/**
 * Initialize the js.
 */
export const init = () => {
    Str.get_string('penaltyrule_group', 'gradepenalty_duedate')
        .done(str => {
            groupLabel = str;
            updateGroupLabel();
        }).fail(notification.exception);
    registerEventListeners();
};
