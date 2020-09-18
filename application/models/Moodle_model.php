<?php
class Moodle_model extends CI_Model {

    function __construct()
    {
	    $this->load->library('MoodleRest');
        parent::__construct();
    }
	
    /* COURSES */
	public function updateCourses($projectId, $courses){
		foreach($courses as $course){
			$courseExists = $this->checkCourseExists($course, $projectId);
			if($courseExists != false && isset($courseExists['items']) && !empty($courseExists['items'])){
				// Update course
				//$this->updateCourse($course, $projectId);
			} else {
				// Create course
				$this->createCourse($course, $projectId);
			}
		}
	}
	
	public function checkCourseExists($courseData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = '619ffb151929e2091601295ce82de3bc';

		$moodleRest = new MoodleRest();
		$moodleRest->setServerAddress($storeUrl.'webservice/rest/server.php');
		$moodleRest->setToken($token);
		$moodleRest->setReturnFormat(MoodleRest::RETURN_ARRAY);

		// Check if category exists
		$search = array(
			'field' => 'idnumber',
			'value' => $courseData['code']
		);
		$course = $moodleRest->request('core_course_get_courses_by_field', $search);
		return array('items' => $course['courses']);
	}
	
	public function createCourse($courseData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = '619ffb151929e2091601295ce82de3bc';

		$moodleRest = new MoodleRest();
		$moodleRest->setServerAddress($storeUrl.'webservice/rest/server.php');
		$moodleRest->setToken($token);
		$moodleRest->setReturnFormat(MoodleRest::RETURN_ARRAY);
		$categoryNumber = $courseData['number'];

		// Check if category exists
		$search = array(
			'criteria' => array(
				array(
					'key' => 'idnumber',
					'value' => $categoryNumber
				)
			)
		);
		$category = $moodleRest->request('core_course_get_categories', $search);
		if(empty($category)){
			// Create category
			$saveData = array(
				'categories' => array(
					array(
						'name' => $courseData['name'],
						'idnumber' => $courseData['number']
					)
				)
			);
			$category = $moodleRest->request('core_course_create_categories', $saveData);
		}
		$categoryId = $category[0]['id'];

		// Create course
		$saveData = array(
			'courses' => array(
				array(
					'fullname' => $courseData['name'],
					'shortname' => $courseData['name'],
					'categoryid' => $categoryId,
					'idnumber' => $courseData['code']
				)
			)
		);
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'beforeCreateCourse')){
				$saveData = $this->$projectModel->beforeCreateCourse($saveData, $courseData, $projectId, 'create');
			}
		}

		$result = $moodleRest->request('core_course_create_courses', $saveData);
		if(isset($result[0])){
			$result = $result[0];
		}
		
		if(isset($result['id']) && $result['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Created course '.$courseData['name'], false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not create course '.$courseData['name'].'. Result: '.print_r($result, true), true);
		}
		return $result;
	}
	
    /* CUSTOMERS */
	public function createCustomer($projectId, $customerData){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = '619ffb151929e2091601295ce82de3bc';

		$customerExists = $this->checkCustomerExists($customerData, $projectId);
		if($customerExists != false && isset($customerExists['items']) && !empty($customerExists['items'])){
			// Update customer
		} else {
			// Create customer
			$saveData = array(
				'users' => array(
					array(
						'username' => strtolower($customerData['email']),//strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $customerData['name'])),
						'firstname' => $customerData['first_name'],
						'lastname' => $customerData['last_name'],
						'email' => $customerData['email'],
						//'phone1' => preg_replace("/[^a-zA-Z0-9]+/", "", $customerData['phone']),
						'password' => '123456789?Se',
						
					)
				)
			);

			if ($customerData['email'] != '') {
				$moodleRest = new MoodleRest();
				$moodleRest->setServerAddress($storeUrl.'webservice/rest/server.php');
				$moodleRest->setToken($token);
				$moodleRest->setReturnFormat(MoodleRest::RETURN_ARRAY);
				$result = $moodleRest->request('core_user_create_users', $saveData);
				if(isset($result[0])){
					$result = $result[0];
				}
				
                if(isset($result['id']) && $result['id'] > 0){
                	apicenter_logs($projectId, 'importcustomers', 'Created customer '.$customerData['email'], false);
                } else {
                	apicenter_logs($projectId, 'importcustomers', 'Could not create customer '.$customerData['email'].'. Result: '.print_r($result, true).', data: '.var_export($saveData, true), true);
                }
                return $result;
            } else {
            	apicenter_logs($projectId, 'importcustomers','Cannot create customer, there is missing info:'. print_r($customerData, true), true);
            }
		}
	}
	
	public function checkCustomerExists($customerData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = '619ffb151929e2091601295ce82de3bc';

		$moodleRest = new MoodleRest();
		$moodleRest->setServerAddress($storeUrl.'webservice/rest/server.php');
		$moodleRest->setToken($token);
		$moodleRest->setReturnFormat(MoodleRest::RETURN_ARRAY);

		// Check if category exists
		$search = array(
			'field' => 'email',
			'values' => array($customerData['email'])
		);
		$user = $moodleRest->request('core_user_get_users_by_field', $search);
		return array('items' => $user);
	}
	
	/* ENROLL */
	public function enrolUser($projectId, $data){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = '619ffb151929e2091601295ce82de3bc';

		// Create enrollment
		$saveData = array(
			'enrolments' => array(
				array(
					'roleid' => 9,
					'userid' => (int)$data['userid'],
					'courseid' => (int)$data['courseid']
				)
			)
		);
		$moodleRest = new MoodleRest();
		$moodleRest->setServerAddress($storeUrl.'webservice/rest/server.php');
		$moodleRest->setToken($token);
		$moodleRest->setReturnFormat(MoodleRest::RETURN_ARRAY);
		$result = $moodleRest->request('enrol_manual_enrol_users', $saveData);
		if(isset($result[0])){
			$result = $result[0];
		}
		return $result;
	}

}