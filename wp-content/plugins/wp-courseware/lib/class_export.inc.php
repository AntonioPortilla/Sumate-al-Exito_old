<?php


/**
 * A class for exporting a selected training course into an XML file.
 */
class WPCW_Export 
{
	
	/**
	 * Default constructor - currently unused.
	 */
	function __construct() { }
	
	
	
	/**
	 * See if there's a course to export based on $_POST variables. If so, trigger the export and XML download.
	 * @param Boolean $triggerFileDownload If true, trigger a file download rather than just XML output as a page.
	 */
	public static function tryExportCourse($triggerFileDownload = true)
	{
		// See if course is being exported
		if (isset($_POST["update"]) && $_POST["update"] == 'wpcw_export' && current_user_can('manage_options'))
		{
			// Now check course is valid. If not, then don't do anything, and let
			// normal form handle the errors.
			$courseID = WPCW_arrays_getValue($_POST, 'export_course_id');
			$courseDetails = WPCW_courses_getCourseDetails($courseID);
			
			if ($courseDetails)
			{
				$moduleList = false;
				
				// Work out what details to fetch and then export
				$whatToExport = WPCW_arrays_getValue($_POST, 'what_to_export');
				switch ($whatToExport)
				{
					// Just the course title, description and settings (no units or modules)
					case 'just_course':
						break;			
						
					// Just the course settings and module settings (no units)
					case 'course_modules':
						$moduleList = WPCW_courses_getModuleDetailsList($courseDetails->course_id);
						break;
						
					// Basically case 'whole_course' - The whole course, modules and units
					default:
						$moduleList = WPCW_courses_getModuleDetailsList($courseDetails->course_id);
						if ($moduleList)
						{
							// Grab units for each module, in the right order, and associate with each module object.
							foreach ($moduleList as $module) 
							{
								// This might return false, but that's OK. We'll check for it later.
								$module->units = WPCW_units_getListOfUnits($module->module_id); 
							}
						}
						
						break;
				}
				
				
				// If true, trigger a file download of the XML file.	
				if ($triggerFileDownload)
				{
					$exportFile = "wp-courseware-export-" . date("Y-m-d") . ".xml";
					header('Content-Description: File Transfer');
					header("Content-Disposition: attachment; filename=$exportFile");					
				}
				
				header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
				
				// When debugging, comment out the line above, and use the following line so that you can see
				// any error messages.
				// header('Content-Type: text/plain');
				
				
				$export = new WPCW_Export();
				echo $export->exportCourseDetails($courseDetails, $moduleList);
												
				die();
			}
		}
		
		// If get here, then normal WPCW processing takes place.
	}
	
	
	/**
	 * Exports the course object, breaking down the course, modules and units. 
	 * @param Object $courseDetails The object containing the course details. 
	 * @param Object $moduleList The object containing the modules and units for this course.
	 * @return String The XML that represents this course.
	 */
	function exportCourseDetails($courseDetails, $moduleList)
	{
		$xml = "";
		
		// Nice whitespace padding to make XML readable.
		$padding = $this->export_indent(false);
		$parentNode = 'course';
		
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= $padding.sprintf('<%s version="%s">', $parentNode, WPCW_DATABASE_VERSION);
				                        
        // #### Add Course Settings
        $fieldsToUse = array(
        	'course_title', 
        	'course_desc', 
        	'course_opt_completion_wall', 
        	'course_opt_user_access',
        	'course_from_name',
        	'course_from_email',
        	'course_to_email',
        	'course_message_unit_complete',
        	'course_message_unit_not_logged_in',
        	'course_message_unit_pending',
        	'course_message_unit_no_access',
        	'course_message_unit_not_yet',        	
        	'email_complete_module_option_admin',
        	'email_complete_module_option',
        	'email_complete_module_subject',
        	'email_complete_module_body',
        	'email_complete_course_option_admin',
        	'email_complete_course_option',
        	'email_complete_course_subject',
        	'email_complete_course_body',
        
        	// 31st May 2013 - V1.26 - Fix - Added, as missing from V1.25
        	'course_message_course_complete',
        	'course_opt_use_certificate',
        );
        	
        $xml .= $this->export_objectToXML('settings', false, $courseDetails, $fieldsToUse, $path);
        
        
        // #### Add Module Details
        if ($moduleList)
        {
        	$xml .= $this->export_startBlock($path, 'modules');

        	// Select which module fields to use in XML
        	$moduleFieldsToUse = array(
        		'module_title', 'module_desc', 'module_order', 'module_number'
        	);
        	
        	// And same for unit fields
        	$unitFieldsToUse = array(
        		'post_title', 'post_content', 'post_name'
        	);        	
        	
        	// Render each module as modules > module > details
        	foreach ($moduleList as $moduleID => $moduleObj) 
        	{
				$parentPath = $path . '/modules';
        		
				// Export main module data
        		$xml .= $this->export_objectToXML('module', false, $moduleObj, $moduleFieldsToUse, $parentPath, false);
        		
        		
        		// Export unit data for this module, mostly just content. The IDs, post date, etc can be ignored.
        		// The ordering is assumed to be the order of the data in the file, so again, that can be ignored.
        		// This makes the XML as simple as possible, making it flexible for the future as possible.
        		if (isset($moduleObj->units) && !empty($moduleObj->units))
        		{
        			$unitParentPath = $parentPath . '/module';
        			$xml .= $this->export_startBlock($unitParentPath, 'units');
        			
        			foreach ($moduleObj->units as $unitObj)
        			{
        				//echo "\n\n\n\n\n\n\n\n";
						//print_r($unitObj);        				
        				$xml .= $this->export_objectToXML('unit', false, $unitObj, $unitFieldsToUse, $unitParentPath . '/units');
        			}
        			
        			$xml .= $this->export_endBlock($unitParentPath, 'units');
        		}
        		
        		
        		// Finally add closing tag for this module
        		$xml .= $this->export_endBlock($parentPath, 'module');
        	}
        	
        	$xml .= $this->export_endBlock($path, 'modules');
        }
        
        // Close parent tag
        $xml .= "$padding</$parentNode>";

        return $xml;
	}
	
	
	/**
	 * Turn an object into XML and return it.
	 * 
	 * @param String $nodeName The name of the block to create from the object.
	 * @param Array $attributes The key => value list of items to save as attributes for the XML block.
	 * @param Object $rawDetails The raw object data.
	 * @param Array $fieldsToUse The list of fields to extract from the raw data into XML.
	 * @param String $path The parent path to export this data to.
	 * @param Boolean $closeTag If true, close the final XML tag. If false, don't add the final section XML tags.
	 * 
	 * @return String The XML for this object.
	 */
	private function export_objectToXML($nodeName, $attributes, $rawDetails, $fieldsToUse, $path, $closeTag = true)
	{
		$padding = $this->export_indent($path);
		$xml = false;
		
		// Open tag with any attributes
		$newPath = "$path/$nodeName";
        $padding = $this->export_indent($newPath);
        $xml .= "$padding<$nodeName";
        
        // See if there are any attributes to add to the node
        if ($attributes)
        {
        	foreach ($attributes as $name => $value)
        	{
        		$xml .= " $name=\"$value\"";
        	}
        }
        // Close tag
        $xml .= '>';		
		
		
        // Only include fields included in our list of details
		foreach ($fieldsToUse as $fieldToUse)
        {
        	if (isset($rawDetails->$fieldToUse)) {
        		$xml .= $this->export_textData($fieldToUse, $rawDetails->$fieldToUse, $newPath.'/'.$fieldName);
        	}
        }
		
        // Closing tag
        if ($closeTag) {
        	$xml .= "$padding</$nodeName>";
        }
		
		return $xml;
	}
	

	/**
	 * Export any data that contains text/HTML, doing it safely to escape characters.
	 * 
	 * @param String $parentNode The name of the XML node to create for this data.
	 * @param String $value The actual data to save.
	 * @param String $path The path of this text data.
	 */
	private function export_textData($parentNode, $value, $path)
	{		
		$xml = "";
		$padding = $this->export_indent($path);
		
		$xml .= "$padding<$parentNode>" . $this->export_cdata($value) . "</$parentNode>";
		
        return $xml;
	}
	
	
	
	

	
	/**
	 * Start a block of content 
	 * @param String $parentPath The current path of the parent object.
	 * @param String $thisPathName The new path string to append.
	 * @return The indented tag.
	 */
	private function export_startBlock($parentPath, $thisPathName)
	{
        $path = "$parentPath/$thisPathName";
        $padding = $this->export_indent($path);
        return "$padding<$thisPathName>";
	}
	
	
	/**
	 * End a block of content 
	 * @param String $parentPath The current path of the parent object.
	 * @param String $thisPathName The new path string to append.
	 * @return The indented tag.
	 */	
	private function export_endBlock($parentPath, $thisPathName)
	{
        $path = "$parentPath/$thisPathName";
        $padding = $this->export_indent($path);
        return "$padding</$thisPathName>";
	}

	
	/**
	 * Export a single line of data in XML.
	 */
	private function export_cdata($value){
        return "<![CDATA[" . htmlspecialchars($value, ENT_QUOTES | ENT_XML1, "UTF-8") . "]]>";
    }
	

	
	/**
	 * Indents the XML according to the depth of the path.
	 */
    private function export_indent($path)
    {
        $depth 	= sizeof(explode("/", $path)) - 1;
        $indent = "";
        $indent = str_pad($indent, $depth, "\t");
        return "\r\n" . $indent;
    }
}


?>