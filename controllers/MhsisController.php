<?php
error_reporting(0);
class Ws_MhsisController extends Base_Base {//controller for a schemesetup

public function init() { //initialization function
		$this->locale = Zend_Registry::get('Zend_Locale');
		$this->view->translate =Zend_Registry::get('Zend_Translate');
   	    Zend_Form::setDefaultTranslator($this->view->translate);
   	    $this->lobjform = new App_Form_Search ();  //local object for Search Form
		$this->lobjschemesetupmodel = new Reports_Model_DbTable_Mhssetup();  
        $this->gobjsessionsis = Zend_Registry::get('sis');

	}

public function indexAction() { //Index Action
		$this->view->title = "Student Profile - Approval to Mahasiswa Table";
	    $this->view->lobjform = $this->lobjform;
	    $lobjPaginator = new App_Model_Common(); // Definitiontype model
        $larrresult =$this->lobjschemesetupmodel->fnGetSchemeDetails2(); 
		if(!$this->_getParam('search'))
   	    unset($this->gobjsessionstudent->schemedetailspaginationresult);
   	    $lintpagecount = $this->gintPageCount;
        $lintpage = $this->_getParam('page',1); // Paginator instance
        if(isset($this->gobjsessionstudent->schemedetailspaginationresult)) {
		$this->view->paginator = $lobjPaginator->fnPagination($this->gobjsessionstudent->schemedetailspaginationresult,$lintpage,$lintpagecount);
		} else {
			$this->view->paginator = $lobjPaginator->fnPagination($larrresult,$lintpage,$lintpagecount);
		}
		
		if ($this->_request->isPost () && $this->_request->getPost ( 'Search' )) {
		$larrformData = $this->_request->getPost ();
		    if ($this->lobjform->isValid ( $larrformData )) {
		     $larrresult = $this->lobjschemesetupmodel->mhsisSearch( $this->lobjform->getValues () ); // Function to Search schemesetup Details
				$this->view->paginator = $lobjPaginator->fnPagination($larrresult,$lintpage,$lintpagecount);
				$this->gobjsessionsis->schemedetailspaginationresult = $larrresult;
			 }
		}
		if ($this->_request->isPost () && $this->_request->getPost ( 'Clear' )) {
			$this->_redirect( $this->baseUrl . '/ws/mhsis/index');
		}
		//----------------------------------------------------------------------------------------------------------------------
		//$auth = Zend_Auth::getInstance();
		
		$mahasiswaDB = new Reports_Model_DbTable_Mhssetup();
		if ($this->getRequest()->isPost()) {
			$formData = $this->getRequest()->getPost();
			//echo"<pre>" ;	print_r ($formData) ;exit();
			$mahasiswaList=$formData['id'];
			unset($formData['id']);
			foreach ($mahasiswaList as $id) {
						$mahasiswaDB->mhsisApprove($formData,$id);

			}
	
			//echo"<pre>" ;	print_r ($formData) ;exit();
		}
		
		////////////////////////////////////////////////////////////////////
	
  }
  
  public function approvelistAction() {
  $mahasiswaDB = new Reports_Model_DbTable_Mhssetup();
  if ($this->getRequest()->isPost()) {
  	$formData = $this->getRequest()->getPost();
  	//echo"<pre>" ;	print_r ($formData) ;exit();
  	$mahasiswaList=$formData['id'];
  	unset($formData['id']);
  	foreach ($mahasiswaList as $id) {
  		$mahasiswaDB->mhsisApprove($formData,$id);
  
  	}
  	$this->_redirect($this->view->url(array('module'=>'ws','controller'=>'mhsis','action'=>'index'),'default',true));
  	
  	
  	//echo"<pre>" ;	print_r ($formData) ;exit();
  	}
  }



public function mhsislistAction(){
	    $id = $this->_getParam('id');
	    //echo"<pre>" ;	print_r ($id) ; exit();
		$lobjSchemesetupForm = new Reports_Form_Mhsisformsetup();
		$this->view->lobjSchemesetupForm = $lobjSchemesetupForm;		
	    $larrresult = $this->lobjschemesetupmodel->fnViewDetails2($id);
	    $this->view->lobjSchemesetupForm->populate($larrresult);
	    if ($this->_request->isPost () && $this->_request->getPost ( 'Save' )) {
    		 	$formData = $this->getRequest()->getPost();
    		 	if ($lobjSchemesetupForm->isValid ( $formData )) {
    		 	$id = $formData ['id']; 
				$this->lobjschemesetupmodel->fnupdateDetails2($formData,$id);//function to update data
				$this->_redirect( $this->baseUrl . '/ws/mhsis/index');
    		 	}
        }
	   if ($this->_request->getPost ( 'Back' )) {
			$this->_redirect( $this->baseUrl . '/ws/mhsis/index');
		}
	}


	
	public function studentviewAction() {
	
		//title
		$this->view->title= $this->view->translate("Government Report - Student View");
		 
		//intake list
		$intakeDB = new App_Model_Record_DbTable_Intake();
		$intakeList = $intakeDB->fngetlatestintake();
		$this->view->intake_list = $intakeList;
		 
		//program list
		$programDb = new Registration_Model_DbTable_Program();
		$programList = $programDb->getProgramByFaculty();
		$this->view->program_list = $programList;
	
		 
		if($this->getRequest()->isXmlHttpRequest() && $this->_request->isPost()){
	
			$formData = $this->getRequest()->getPost();
	
			$this->_helper->layout->disableLayout();
			 
			$ajaxContext = $this->_helper->getHelper('AjaxContext');
			$ajaxContext->addActionContext('view', 'html');
			$ajaxContext->initContext();
	
			$ajaxContext->addActionContext('view', 'html')
			->addActionContext('form', 'html')
			->addActionContext('process', 'json')
			->initContext();
	
			/*
			 * Search Student
			*/
	
			$db = Zend_Db_Table::getDefaultAdapter();
	
			$lstrSelect = $db->select()->from(array('sa' => 'tbl_studentregistration'), array('sa.*'))
			->joinLeft(array('sp'=>'student_profile'),'sp.appl_id=sa.IdApplication',array('burekol_verify_date','appl_fname','appl_mname','appl_lname','appl_lname','appl_birth_place','appl_dob','appl_gender','appl_religion'))
			->joinLeft(array('deftn' => 'tbl_definationms'), 'deftn.idDefinition=sa.Status', array('deftn.DefinitionCode'))
			->joinLeft(array('prg' => 'tbl_program'), 'prg.IdProgram=sa.IdProgram', array('prg.ArabicName','ProgramName'))
			->joinLeft(array('fac' => 'tbl_collegemaster'), 'fac.IdCollege = prg.IdCollege', array('faculty_name'=>'ShortName'))
			->joinLeft(array('intk' => 'tbl_intake'), 'intk.IdIntake=sa.IdIntake', array('intk.IntakeId'))
			->where("sa.OldIdStudentRegistration IS NULL")
			->where("sa.IdIntake = ?",$formData['intake'])
			->order("sa.registrationId ASC");
	
			if($formData['program']!='null'){
				$lstrSelect->where('prg.IdProgram = ? ', $formData['program']);
			}
	
			$lstrSelect->group('sp.appl_id');
	
			//echo $lstrSelect;

			$result = $db->fetchAll($lstrSelect);
			$this->view->studentview = $result;
	
			$json = Zend_Json::encode($result);
	
			echo $json;
			exit();
		}
	}
}












