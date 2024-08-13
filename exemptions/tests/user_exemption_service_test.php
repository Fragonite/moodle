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

namespace core_exemptions;

use core_exemptions\local\entity\exemption;

/**
 * Test class covering the component_exemption_service within the service layer of exemptions.
 *
 * @package    core_exemptions
 * @category   test
 * @copyright  2018 Jake Dallimore <jrhdallimore@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_exemption_service_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    // Basic setup stuff to be reused in most tests.
    protected function setup_users_and_courses() {
        $user1 = self::getDataGenerator()->create_user();
        $user1context = \context_user::instance($user1->id);
        $user2 = self::getDataGenerator()->create_user();
        $user2context = \context_user::instance($user2->id);
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $course1context = \context_course::instance($course1->id);
        $course2context = \context_course::instance($course2->id);
        return [$user1context, $user2context, $course1context, $course2context];
    }

    /**
     * Generates an in-memory repository for testing, using an array store for CRUD stuff.
     *
     * @param array $mockstore
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function get_mock_repository(array $mockstore) {
        // This mock will just store data in an array.
        $mockrepo = $this->getMockBuilder(\core_exemptions\local\repository\exemption_repository_interface::class)
            ->onlyMethods([])
            ->getMock();
        $mockrepo->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function(exemption $exemption) use (&$mockstore) {
                // Mock implementation of repository->add(), where an array is used instead of the DB.
                // Duplicates are confirmed via the unique key, and exceptions thrown just like a real repo.
                $key = $exemption->component . $exemption->itemtype . $exemption->itemid
                    . $exemption->contextid;

                // Check the objects for the unique key.
                foreach ($mockstore as $item) {
                    if ($item->uniquekey == $key) {
                        throw new \moodle_exception('exemption already exists');
                    }
                }
                $index = count($mockstore);     // Integer index.
                $exemption->uniquekey = $key;   // Simulate the unique key constraint.
                $exemption->id = $index;
                $mockstore[$index] = $exemption;
                return $mockstore[$index];
            })
        );
        $mockrepo->expects($this->any())
            ->method('find_by')
            ->will($this->returnCallback(function(array $criteria, int $limitfrom = 0, int $limitnum = 0) use (&$mockstore) {
                // Check for single value key pair vs multiple.
                $multipleconditions = [];
                foreach ($criteria as $key => $value) {
                    if (is_array($value)) {
                        $multipleconditions[$key] = $value;
                        unset($criteria[$key]);
                    }
                }

                // Initialise the return array.
                $returns = [];

                // Check the mockstore for all objects with properties matching the key => val pairs in $criteria.
                foreach ($mockstore as $index => $mockrow) {
                    $mockrowarr = (array)$mockrow;
                    if (array_diff_assoc($criteria, $mockrowarr) == []) {
                        $found = true;
                        foreach ($multipleconditions as $key => $value) {
                            if (!in_array($mockrowarr[$key], $value)) {
                                $found = false;
                                break;
                            }
                        }
                        if ($found) {
                            $returns[$index] = $mockrow;
                        }
                    }
                }
                // Return a subset of the records, according to the paging options, if set.
                if ($limitnum != 0) {
                    return array_slice($returns, $limitfrom, $limitnum);
                }
                // Otherwise, just return the full set.
                return $returns;
            })
        );
        $mockrepo->expects($this->any())
            ->method('find_exemption')
            ->will($this->returnCallback(function (string $comp, string $type, int $id, int $ctxid) use (&$mockstore) {
                // Check the mockstore for all objects with properties matching the key => val pairs in $criteria.
                $crit = ['component' => $comp, 'itemtype' => $type, 'itemid' => $id, 'contextid' => $ctxid];
                foreach ($mockstore as $fakerow) {
                    $fakerowarr = (array)$fakerow;
                    if (array_diff_assoc($crit, $fakerowarr) == []) {
                        return $fakerow;
                    }
                }
                throw new \dml_missing_record_exception("Item not found");
            })
        );
        $mockrepo->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function(int $id) use (&$mockstore) {
                return $mockstore[$id];
            })
        );
        $mockrepo->expects($this->any())
            ->method('exists')
            ->will($this->returnCallback(function(int $id) use (&$mockstore) {
                return array_key_exists($id, $mockstore);
            })
        );
        $mockrepo->expects($this->any())
            ->method('count_by')
            ->will($this->returnCallback(function(array $criteria) use (&$mockstore) {
                $count = 0;
                // Check the mockstore for all objects with properties matching the key => val pairs in $criteria.
                foreach ($mockstore as $index => $mockrow) {
                    $mockrowarr = (array)$mockrow;
                    if (array_diff_assoc($criteria, $mockrowarr) == []) {
                        $count++;
                    }
                }
                return $count;
            })
        );
        $mockrepo->expects($this->any())
            ->method('delete')
            ->will($this->returnCallback(function(int $id) use (&$mockstore) {
                foreach ($mockstore as $mockrow) {
                    if ($mockrow->id == $id) {
                        unset($mockstore[$id]);
                    }
                }
            })
        );
        $mockrepo->expects($this->any())
            ->method('exists_by')
            ->will($this->returnCallback(function(array $criteria) use (&$mockstore) {
                // Check the mockstore for all objects with properties matching the key => val pairs in $criteria.
                foreach ($mockstore as $index => $mockrow) {
                    $mockrowarr = (array)$mockrow;
                    if (array_diff_assoc($criteria, $mockrowarr) == []) {
                        return true;
                    }
                }
                return false;
            })
        );
        return $mockrepo;
    }

    /**
     * Test getting a component_exemption_service from the static locator.
     */
    public function test_get_service_for_component(): void {
        $userservice = \core_exemptions\service_factory::get_service_for_component('core_course');
        $this->assertInstanceOf(\core_exemptions\local\service\component_exemption_service::class, $userservice);
    }

    /**
     * Test confirming an item can be exempt only once.
     */
    public function test_create_exemption_basic(): void {
        [$user1context, $user2context, $course1context, $course2context] = $this->setup_users_and_courses();

        // Get a component_exemption_service for a user.
        $repo = $this->get_mock_repository([]); // Mock repository, using the array as a mock DB.
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption a course.
        $exemption1 = $service->create_exemption('course', $course1context->instanceid, $course1context);
        $this->assertObjectHasProperty('id', $exemption1);

        // Try to exemption the same course again.
        $this->expectException('moodle_exception');
        $service->create_exemption('course', $course1context->instanceid, $course1context);
    }

    /**
     * Test confirming that an exception is thrown if trying to create an item for a non-existent component.
     */
    public function test_create_exemption_nonexistent_component(): void {
        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]); // Mock repository, using the array as a mock DB.

        // Try to exemption something in a non-existent component.
        $this->expectException('moodle_exception');
        $service = new \core_exemptions\local\service\component_exemption_service('core_cccourse', $repo);
    }

    /**
     * Test fetching exemptions for single user, by area.
     */
    public function test_find_exemptions_by_type_single_user(): void {
        [$user1context, $user2context, $course1context, $course2context] = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]); // Mock repository, using the array as a mock DB.
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // Exemption in 2 courses, in separate areas.
        $exem1 = $service->create_exemption('course', $course1context->instanceid, $course1context);
        $exem2 = $service->create_exemption('anothertype', $course2context->instanceid, $course2context);

        // Verify we can get exemptions by area.
        $exemptions = $service->find_exemptions_by_type('course');
        $this->assertIsArray($exemptions);
        $this->assertCount(1, $exemptions); // We only get exemptions for the 'core_course/course' area.
        $this->assertEquals($exem1->id, $exemptions[$exem1->id]->id);

        $exemptions = $service->find_exemptions_by_type('anothertype');
        $this->assertIsArray($exemptions);
        $this->assertCount(1, $exemptions); // We only get exemptions for the 'core_course/course' area.
        $this->assertEquals($exem2->id, $exemptions[$exem2->id]->id);
    }

    /**
     * Test fetching exemptions for single user, by area.
     */
    public function test_find_all_exemptions(): void {
        [$user1context, $user2context, $course1context, $course2context] = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]); // Mock repository, using the array as a mock DB.
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption 2 courses, in separate areas.
        $exem1 = $service->create_exemption('course', $course1context->instanceid, $course1context);
        $exem2 = $service->create_exemption('anothertype', $course2context->instanceid, $course2context);
        $exem3 = $service->create_exemption('yetanothertype', $course2context->instanceid, $course2context);

        // Verify we can get exemptions by area.
        $exemptions = $service->find_all_exemptions(['course']);
        $this->assertIsArray($exemptions);
        $this->assertCount(1, $exemptions); // We only get exemptions for the 'core_course/course' area.
        $this->assertEquals($exem1->id, $exemptions[$exem1->id]->id);

        $exemptions = $service->find_all_exemptions(['course', 'anothertype']);
        $this->assertIsArray($exemptions);
        // We only get exemptions for the 'core_course/course' and 'core_course/anothertype area.
        $this->assertCount(2, $exemptions);
        $this->assertEquals($exem1->id, $exemptions[$exem1->id]->id);
        $this->assertEquals($exem2->id, $exemptions[$exem2->id]->id);

        $exemptions = $service->find_all_exemptions();
        $this->assertIsArray($exemptions);
        $this->assertCount(3, $exemptions); // We only get exemptions for the 'core_cours' area.
        $this->assertEquals($exem2->id, $exemptions[$exem2->id]->id);
        $this->assertEquals($exem1->id, $exemptions[$exem1->id]->id);
        $this->assertEquals($exem3->id, $exemptions[$exem3->id]->id);
    }

    /**
     * Test confirming the pagination support for the find_exemptions_by_type() method.
     */
    public function test_find_exemptions_by_type_pagination(): void {
        [$user1context, $user2context, $course1context, $course2context] = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]);
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption 10 arbitrary items.
        foreach (range(1, 10) as $i) {
            $service->create_exemption('course', $i, $course1context);
        }

        // Verify we have 10 exemptions.
        $this->assertCount(10, $service->find_exemptions_by_type('course'));

        // Verify we get back 5 exemptions for page 1.
        $exemptions = $service->find_exemptions_by_type('course', 0, 5);
        $this->assertCount(5, $exemptions);

        // Verify we get back 5 exemptions for page 2.
        $exemptions = $service->find_exemptions_by_type('course', 5, 5);
        $this->assertCount(5, $exemptions);

        // Verify we get back an empty array if querying page 3.
        $exemptions = $service->find_exemptions_by_type('course', 10, 5);
        $this->assertCount(0, $exemptions);
    }

    /**
     * Test confirming the basic deletion behaviour.
     */
    public function test_delete_exemption_basic(): void {
        [$user1context, $user2context, $course1context, $course2context] = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]);
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption a course.
        $exem1 = $service->create_exemption('course', $course1context->instanceid, $course1context);
        $this->assertTrue($repo->exists($exem1->id));

        // Delete the exemption.
        $service->delete_exemption('course', $course1context->instanceid, $course1context);

        // Verify the exemption doesn't exist.
        $this->assertFalse($repo->exists($exem1->id));

        // Try to delete a exemption which we know doesn't exist.
        $this->expectException(\moodle_exception::class);
        $service->delete_exemption('course', $course1context->instanceid, $course1context);
    }

    /**
     * Test confirming the behaviour of the exemption_exists() method.
     */
    public function test_exemption_exists(): void {
        list($user1context, $user2context, $course1context, $course2context) = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]);
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption a course.
        $exem1 = $service->create_exemption('course', $course1context->instanceid, $course1context);

        // Verify we can check existence of the exemption.
        $this->assertTrue(
            $service->exemption_exists(
                'core_course',
                'course',
                $course1context->instanceid,
                $course1context
            )
        );

        // And one that we know doesn't exist.
        $this->assertFalse(
            $service->exemption_exists(
                'core_course',
                'someothertype',
                $course1context->instanceid,
                $course1context
            )
        );
    }

    /**
     * Test confirming the behaviour of the get_exemption() method.
     */
    public function test_get_exemption(): void {
        list($user1context, $user2context, $course1context, $course2context) = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]);
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption a course.
        $exem1 = $service->create_exemption('course', $course1context->instanceid, $course1context);

        $result = $service->get_exemption(
            'core_course',
            'course',
            $course1context->instanceid,
            $course1context
        );
        // Verify we can get the exemption.
        $this->assertEquals($exem1->id, $result->id);

        // And one that we know doesn't exist.
        $this->assertNull(
            $service->get_exemption(
                'core_course',
                'someothertype',
                $course1context->instanceid,
                $course1context
            )
        );
    }

    /**
     * Test confirming the behaviour of the count_exemptions_by_type() method.
     */
    public function test_count_exemptions_by_type(): void {
        list($user1context, $user2context, $course1context, $course2context) = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        $repo = $this->get_mock_repository([]);
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        $this->assertEquals(0, $service->count_exemptions_by_type('core_course', 'course', $course1context));
        // exemption a course.
        $service->create_exemption('course', $course1context->instanceid, $course1context);

        $this->assertEquals(1, $service->count_exemptions_by_type('core_course', 'course', $course1context));

        // exemption another course.
        $service->create_exemption('course', $course2context->instanceid, $course1context);

        $this->assertEquals(2, $service->count_exemptions_by_type('core_course', 'course', $course1context));

        // exemption a course in another context.
        $service->create_exemption('course', $course2context->instanceid, $course2context);

        // Doesn't affect original context.
        $this->assertEquals(2, $service->count_exemptions_by_type('core_course', 'course', $course1context));
        // Gets counted if we include all contexts.
        $this->assertEquals(3, $service->count_exemptions_by_type('core_course', 'course'));
    }

    /**
     * Verify that the join sql generated by get_join_sql_by_type is valid and can be used to include exemption information.
     */
    public function test_get_join_sql_by_type(): void {
        global $DB;
        list($user1context, $user2context, $course1context, $course2context) = $this->setup_users_and_courses();

        // Get a component_exemption_service for the user.
        // We need to use a real (DB) repository, as we want to run the SQL.
        $repo = new \core_exemptions\local\repository\exemption_repository();
        $service = new \core_exemptions\local\service\component_exemption_service('core_course', $repo);

        // exemption the first course only.
        $service->create_exemption('course', $course1context->instanceid, $course1context);

        // Generate the join snippet.
        list($exemsql, $exemparams) = $service->get_join_sql_by_type('core_course', 'course', 'exemalias', 'c.id');

        // Join against a simple select, including the 2 courses only.
        $params = ['courseid1' => $course1context->instanceid, 'courseid2' => $course2context->instanceid];
        $params = $params + $exemparams;
        $records = $DB->get_records_sql("SELECT c.id, exemalias.component
                                           FROM {course} c $exemsql
                                          WHERE c.id = :courseid1 OR c.id = :courseid2", $params);

        // Verify the exemption information is returned, but only for the exempt course.
        $this->assertCount(2, $records);
        $this->assertEquals('core_course', $records[$course1context->instanceid]->component);
        $this->assertEmpty($records[$course2context->instanceid]->component);
    }
}
