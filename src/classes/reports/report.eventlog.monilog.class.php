<?php
/*
	*********************************************************************
	* -> www.phplogcon.org <-											*
	* -----------------------------------------------------------------	*
	* Some constants													*
	*																	*
	* Monilog Report is a basic report for EventLog and Syslog data
	*
	* \version 1.0.0 Init Version
	*																	*
	* All directives are explained within this file						*
	*
	* Copyright (C) 2008-2009 Adiscon GmbH.
	*
	* This file is part of phpLogCon.
	*
	* PhpLogCon is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation, either version 3 of the License, or
	* (at your option) any later version.
	*
	* PhpLogCon is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with phpLogCon. If not, see <http://www.gnu.org/licenses/>.
	*
	* A copy of the GPL can be found in the file "COPYING" in this
	* distribution.
	*********************************************************************
*/

// --- Avoid directly accessing this file! 
if ( !defined('IN_PHPLOGCON') )
{
	die('Hacking attempt');
	exit;
}
// --- 

// --- Basic Includes!
require_once($gl_root_path . 'classes/reports/report.class.php');
// --- 

class Report_monilog extends Report {
	// Common Properties
	public $_reportVersion = 1;									// Internally Version of the ReportEngine
	public $_reportID = "report.eventlog.monilog.class";		// ID for the report, needs to be unique!
	public $_reportFileBasicName = "report.eventlog.monilog";	// Basic Filename for reportfiles
	public $_reportTitle = "EventLog Summary Report";			// Display name for the report
	public $_reportDescription = "This is a EventLog Summary Report based on Monilog";
	public $_reportHelpArticle = "";
	public $_reportNeedsInit = false;							// True means that this report needs additional init stuff
	public $_reportInitialized = false;							// True means report is installed

/*	private $_currentOffset = -1;
	private $_currentStartPos = -1;
	private $_fp = null;
	private $_bEOS = false;

	const _BUFFER_length = 8192;
	private $_buffer = false;
	private $_buffer_length = 0;
	private $_p_buffer = -1;
*/

	// Constructor
	public function Report_monilog() {
//		$this->_logStreamConfigObj = $streamConfigObj;

		// Fill fields we need for this report
		$this->_arrProperties[] = SYSLOG_UID;
		$this->_arrProperties[] = SYSLOG_DATE;
		$this->_arrProperties[] = SYSLOG_HOST;
		$this->_arrProperties[] = SYSLOG_MESSAGETYPE;
		$this->_arrProperties[] = SYSLOG_FACILITY;
		$this->_arrProperties[] = SYSLOG_SEVERITY;
		$this->_arrProperties[] = SYSLOG_EVENT_ID;
		$this->_arrProperties[] = SYSLOG_EVENT_LOGTYPE;
		$this->_arrProperties[] = SYSLOG_EVENT_SOURCE;
		$this->_arrProperties[] = SYSLOG_EVENT_CATEGORY;
		$this->_arrProperties[] = SYSLOG_EVENT_USER;
		$this->_arrProperties[] = SYSLOG_MESSAGE;

	}

	/**
	* startDataProcessing, analysing data
	*
	* @param arrProperties array in: Properties wish list.
	* @return integer Error stat
	*/
	public function startDataProcessing()
	{
		global $content, $severity_colors; 

		// Verify Datasource first!
		if ( $this->verifyDataSource() == SUCCESS ) 
		{
			// Test opening the stream
			$res = $this->_streamObj->Open( $this->_arrProperties, true );
			if ( $res == SUCCESS )
			{
				// --- Report logic starts here

				// Step 1: Gather Summaries 
				// Obtain data from the logstream!
				$content["report_summary"] = $this->_streamObj->ConsolidateDataByField( SYSLOG_SEVERITY, 10, SYSLOG_SEVERITY, SORTING_ORDER_DESC, null, false );

				// If data is valid, we have an array!
				if ( is_array($content["report_summary"]) && count($content["report_summary"]) > 0 )
				{
					foreach ($content["report_summary"] as &$tmpReportData )
					{
						$tmpReportData['DisplayName'] = GetSeverityDisplayName( $tmpReportData[SYSLOG_SEVERITY] );
						$tmpReportData['bgcolor'] = $severity_colors[ $tmpReportData[SYSLOG_SEVERITY] ];
					}
				}

				// Get List of hosts
				$content["report_computers"] = $this->_streamObj->ConsolidateItemListByField( SYSLOG_HOST, 20, SYSLOG_HOST, SORTING_ORDER_DESC );

				// This function will consolidate the Events based per Host!
				$this->ConsolidateEventsPerHost();

/*				// If data is valid, we have an array!
				if ( is_array($content["report_computers"]) && count($content["report_computers"]) > 0 )
				{
					foreach ($content["report_computers"] as &$tmpReportComputer )
					{
						$tmpReportComputer['report_events'] = $this->_streamObj->ConsolidateDataByField( SYSLOG_MESSAGE, 100, SYSLOG_MESSAGE, SORTING_ORDER_DESC, null, false );

						print_r ( $tmpReportComputer['report_events'] );
					}
				}
*/

print_r ( $content["report_computers"] );
exit;

				// ---

			}

		}
		
		// Return success!
		return SUCCESS;
	}


	/**
	* verifyDataSource, verifies if data is accessable and 
	* contains what we need
	*
	* @param arrProperties array in: Properties wish list.
	* @return integer Error stat
	*/
	public function verifyDataSource()
	{
		global $content; 

		if ( $this->_streamCfgObj == null ) 
		{
			if ( isset($content['Sources'][$this->_mySourceID]['ObjRef']) )
			{
				// Obtain and get the Config Object
				$this->_streamCfgObj = $content['Sources'][$this->_mySourceID]['ObjRef'];

				// Fix Filename manually for FILE LOGSTREAM!
				if ( $content['Sources'][$this->_mySourceID]['SourceType'] == SOURCE_DISK ) 
					$this->_streamCfgObj->FileName = CheckAndPrependRootPath(DB_StripSlahes($content['Sources'][$this->_mySourceID]['DiskFile']));
			}
			else
				return ERROR_SOURCENOTFOUND;
		}

		if ( $this->_streamObj == null ) 
		{
			// Create LogStream Object 
			$this->_streamObj = $this->_streamCfgObj->LogStreamFactory($this->_streamCfgObj);
		}

		// Check datasource and return result
		$res = $this->_streamObj->Verify();
		return $res;
	}
	

	/**
	* InitReport, empty
	*
	*/
	public function InitReport()
	{
		// Nothing todo
		return SUCCESS;
	}


	/**
	* RemoveReport, empty
	*
	*/
	public function RemoveReport()
	{
		// Nothing todo
		return SUCCESS;
	}


	/**
	* validateLicense, check license code
	*
	*/
	public function validateLicense()
	{
		// This is a free report!
		return SUCCESS;
	}


	// --- Private functions...


	/**
	*	Helper function to consolidate events 
	*/
	private function ConsolidateEventsPerHost()
	{
		// Create array with columns we need for analysis
		$reportFields[] = SYSLOG_UID;
		$reportFields[] = SYSLOG_DATE;
		$reportFields[] = SYSLOG_HOST;
		$reportFields[] = SYSLOG_SEVERITY;
		$reportFields[] = SYSLOG_EVENT_ID;
		$reportFields[] = SYSLOG_EVENT_SOURCE;
		$reportFields[] = SYSLOG_MESSAGE;
		
		// Set Filter string
		$this->_streamObj->SetFilter( $this->_filterString );

		// Now open the stream for data processing
		$res = $this->_streamObj->Open( $reportFields, true );
		if ( $res == SUCCESS )
		{



		}

		// Work done!
		return SUCCESS;
	}
/*
	private function ResetBuffer() {
		$this->_bEOS = false;
		$this->_buffer = false;
		$this->_buffer_length = 0;
		$this->_p_buffer = -1;
	}
*/


}

?>