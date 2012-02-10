<?php
jimport('joomla.error.log');

/**
*@author  The-Di-Lab
*@email   thedilab@gmail.com
*@website http://www.the-di-lab.com
*@version 1.2
**/
class Calendar {   
	
	/**
     * Constructor
     */
	public function __construct()
	{
		date_default_timezone_set($this->timezone);
		$this->naviHref = htmlentities($_SERVER['PHP_SELF']);
	}
	
	/********************* PROPERTY ********************/
	/**
    * Description
    *
    * @access private
    * @var type string/array
    */	
    private $dayLabels = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
    private $timezone = 'America/Toronto';
    private $currentYear=0;
    private $currentMonth=0;
    private $currentDay=0;
    private $currentDate=null;
    private $daysInMonth=0;
    private $sundayFirst=true;  
	private $naviHref= null;
	private $showToday = false;
    
    /********************* PUBLIC **********************/  
    /**
    * Set week labels' order. 
    * When it is set to false, 
    * monday will be listed as the first day.
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              public
    * @param               boolean
    * @return              void
    */
	public function setSundayFirst($bool=true){
		$this->sundayFirst=$bool;
	}
	
    /**
    * set timzeone 
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              public
    * @param               string
    * @return              void
    */
	public function setTimezone($timezone){
		$this->timezone = $timezone;
	}
	
    /**
    * set navi href 
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              public
    * @param               string
    * @return              void
    */
	public function setNaviHref($href){
		$this->naviHref=$href;
	}
	
    /**
    * highlight current date
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              public
    * @param               string
    * @return              void
    */
	public function showToday($bool){
		$this->showToday=$bool;
	}
	
	
    /**
    * print out the calendar 
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              public
    * @param               string
    * @param               string
    * @param               array
    * @return              string
    */
	public function show($month=null,$year=null,$attributes=false){
		
		error_log("calendar.php: theMonth/theYear " . $month . "/" . $year . " \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		if(isset($_GET['navi'])){
			$year = $_GET['year'];
		}else if(null==$year){
			$year = date("Y",time());	
		}
		
		if(isset($_GET['navi'])){
			$month = $_GET['month'];
		}else if(null==$month){
			$month = date("m",time());
		}
		
		
		$this->currentYear=$year;
		$this->currentMonth=$month;
		$this->daysInMonth=$this->_daysInMonth($month,$year);	
		
		$content='<div id="calendar">'.
					$this->_createNavi().
				 '<ul>'.$this->_createLabels();					
		for($i=0;$i<$this->_weeksInMonth($month,$year);$i++){
			for($j=1;$j<=7;$j++){
				$content.=$this->_showDay($i*7+$j,$attributes);
			}
		}
		$content.='</ul></div>';
		return $content;	
	}
	
    /********************* PRIVATE **********************/  
    /**
    * create the li element for ul
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               string
    * @param               array
    * @return              string
    */
	private function _showDay($cellNumber,$attributes=false){ 
		if($this->currentDay==0){
			//1 (for Monday) through 7 (for Sunday)
			$firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));
			if($this->sundayFirst){
				if($firstDayOfTheWeek==7){
					$firstDayOfTheWeek=1;
				}else{
					$firstDayOfTheWeek++;
				}
			}			
			if(intval($cellNumber) == intval($firstDayOfTheWeek)){
				$this->currentDay=1;
			}
		}
		
	    if(($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth)){
	    	$this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
			$cellContent = $this->_createCellContent($attributes);
			$this->currentDay++;	
		}else{
			$this->currentDate =null;
			$cellContent=null;
		}
		
		$liClass='';		
		if($this->showToday){			
			if( ($this->currentDate) ==  (date('Y-m-d')) ) {
				$liClass='today';				
			}
		}
		
		//TODO: make this more robust -- allow user's to view old appointments
		// if the date can't be booked (i.e. in the past, or beyond the booking limit), change the way it's displayed
		
		// allow edits if the appointment is more than 2 days from now
		$now = new DateTime();
// 		$date2 = new DateTime($this->currentDate);
		$theDiff = strtotime($this->currentDate) - time();
		$weeks = $theDiff / 60 / 60 / 24 / 7;
		
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$maxScheduledWeeks = $configOptions->get('schedule_length',8);
// 		if ( $days > $cancellationAllowedMinDays && $now < $date2 )
		if ( $weeks <= $maxScheduledWeeks )
		{
// 			$editButton = "<input type='button' name='edit' value='" . JText::_('COM_SALONBOOK_LIST_BUTTON_CHANGE_TITLE') . "' onclick='prepareToEdit(" . $appointment['id'] . "); ' class='apptEditButton_change' />";
			$withinRange = true;
		}
		else 
		{
			$withinRange = false;
		}
		
		if ( strtotime($this->currentDate) - time() < 0 || $withinRange == false)
		{
			$liClass .= ' blockedDate';
			$onclickLink = '';
		}
		else
		{
// 			$onclickLink = ' onclick="'.((isset($config['link'])&&isset($config['link']['href']))?$config['link']['href']:htmlentities($_SERVER['PHP_SELF']))."('".$this->currentDate."') ".'" ';
			$onclickLink = ' onclick="javascript:calDateSelected(\'' . $this->currentDate. '\')" ';
		}
		
		return '<li ' . $onclickLink . ' id="li-'.$this->currentDate.'" class="'.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
				($cellContent==null?'inactive':'').'  '.$liClass.'">'.$cellContent.'</li>';
	}
	
	/**
    * create navigation 
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @return              string
    */
	private function _createNavi(){
		$nextMonth = $this->currentMonth==12?1:intval($this->currentMonth)+1;
		$nextYear = $this->currentMonth==12?intval($this->currentYear)+1:$this->currentYear;
		
		$preMonth = $this->currentMonth==1?12:intval($this->currentMonth)-1;
		$preYear = $this->currentMonth==1?intval($this->currentYear)-1:$this->currentYear;
		
// 		return 		
// 			'<div id="navi">'.
// 			'<div id="prev"><a class="button" href="'.$this->naviHref.'?month='.$preMonth.'&year='.$preYear.'&navi=1"><span>Prev</span></a></div>'.
// 				date('Y M',strtotime($this->currentYear.'-'.$this->currentMonth.'-1')).
// 			'<div id="next"><a class="button" href="'.$this->naviHref.'?month='.$nextMonth.'&year='.$nextYear.'&navi=1"><span>Next</span></a></div>'.
// 			'</div>'.
// 			'<div class="seperator"></div><div class="clear"></div>';

			return 		
			'<div id="navi">'.
				'<div id="prev"><a class="button" href="javascript:ajxShowCalendarMonth('.$preMonth.','.$preYear.')"><span>' . date('M',strtotime($preYear.'-'.$preMonth)) . '</span></a></div>'.
				date('F Y',strtotime($this->currentYear.'-'.$this->currentMonth.'-1')).
				'<div id="next"><a class="button" href="javascript:ajxShowCalendarMonth('. $nextMonth .','. $nextYear .')"><span>' . date('M',strtotime($nextYear.'-'.$nextMonth)) . '</span></a></div>'.
			'</div>'.
			'<div class="seperator"></div><div class="clear"></div>';
	}
		
	/**
    * create calendar week labels
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @return              string
    */
	private function _createLabels(){
		if($this->sundayFirst){
			$temp = $this->dayLabels[0];
			for($i=1;$i<sizeof($this->dayLabels);$i++){
				$tmp = $this->dayLabels[$i];
				$this->dayLabels[$i]=$temp;
				$temp=$tmp;
			}
			$this->dayLabels[0]=$temp;
		}
				
		$content='';
		foreach($this->dayLabels as $index=>$label){
			$content.='<li class="'.($label==6?'end title':'start title').' title">'.$label.'</li>';
		}
		
		return $content;
	}
	
    /**
    * create content for li element
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               array
    * @return              string
    */	
	private function _createCellContent($setting=false){
		$content='';		
		
		$content= $this->_label() ;
		
		if(isset($setting['type'])){
			if(isset($setting['type']['link'])||(is_array($setting['type'])&&in_array('link',$setting['type']))){ 
				$this->_link($content,$setting['type']) ;
			}
				
			if(isset($setting['type']['checkbox'])||(is_array($setting['type'])&&in_array('checkbox',$setting['type']))){
				$this->_checkbox($content,$setting['type']) ;
			}			
			
			if(isset($setting['type']['radio'])||(is_array($setting['type'])&&in_array('radio',$setting['type']))){
				$this->_radio($content,$setting['type']) ;
			}	
		}
		
		return $content;
	}
	
    /**
    * create a simple text
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               string
    * @param               array
    * @return              string
    */
	private function _label($config=false){
		return $this->currentDay;
	}
	
    /**
    * create a link and append to label
    * Modified to pass through a date to a javascript function.
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               string
    * @param               array
    * @return              string
    */	
	private function _link(&$content,$config=false){
// 		$name = "link_date";

// 		return $content='<a id="a-'.$this->currentDate.'" class="common" href="'.
// 					((isset($config['link'])&&isset($config['link']['href']))?$config['link']['href']:htmlentities($_SERVER['PHP_SELF'])).
// 				'?date='.$this->currentDate.'">'.$content.'</a>';	
		
// 		$linkHref = ' href="'.((isset($config['link'])&&isset($config['link']['href']))?$config['link']['href']:htmlentities($_SERVER['PHP_SELF']))."('".$this->currentDate."') ".'" ';
				
// 		if ( strtotime($this->currentDate) - time() < 0)
// 		{
// 			$linkHref = " ";
// 		}
		
		$linkHref = " ";
		
// 		return $content='<a id="a-'.$this->currentDate.'" class="common" '. $linkClass .' '.$linkHref.'>'.$content.'</a>';
		return $content='<a id="a-'.$this->currentDate.'" class="common" '.' '.$linkHref.'>'.$content.'</a>';
		
	}

	/**
    * create a checkbox and append to label
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               string
    * @param               array
    * @return              string
    */
	private function _checkbox(&$content,$config=false){
		$name = "checkbox_date[]";
		return $content='<input id="checkbox-'.$this->currentDate.'" value="'.$this->currentDate.'" type="checkbox" name="'.$name.'">'.$content.'</input>';
	}
	
    /**
    * create a radio box and append to label
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               string
    * @param               array
    * @return              string
    */
	private function _radio(&$content,$config=false){
		$name="radio_date";
		return $content='<input id="radio-'.$this->currentDate.'" value="'.$this->currentDate.'" type="radio" name="'.$name.'">'.$content.'</input>';
	}

    /**
    * calculate number of weeks in a particular month
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               number
    * @param               number
    * @return              number
    */
	private function _weeksInMonth($month=null,$year=null){
		if(null==($year))
			$year =  date("Y",time());	

		if(null==($month))
			$month = date("m",time());
						
		// find number of weeks in this month
		$daysInMonths = $this->_daysInMonth($month,$year);
		
		$numOfweeks = ($daysInMonths%7==0?0:1) + intval($daysInMonths/7);
		$monthEndingDay= date('N',strtotime($year.'-'.$month.'-'.$daysInMonths));
		$monthStartDay = date('N',strtotime($year.'-'.$month.'-01'));
		$monthEndingDay==7?$monthEndingDay=0:'';
		$monthStartDay==7?$monthStartDay=0:'';
		
		if($monthEndingDay<$monthStartDay){
			$numOfweeks++;
		}
		return $numOfweeks;
		
	}

	/**
    * calculate number of days in a particular month
    *
    * @author              The-Di-Lab <thedilab@gmail.com>
    * @access              private
    * @param               number
    * @param               number
    * @return              number
    */
	private function _daysInMonth($month=null,$year=null){
		if(null==($year))
			$year =  date("Y",time());	

		if(null==($month))
			$month = date("m",time());
			
		return date('t',strtotime($year.'-'.$month.'-01'));
	}
	
}