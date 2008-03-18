/* 
Helper Javascript Constants
*/
const DATEMODE_ALL = 1, DATEMODE_RANGE = 2, DATEMODE_LASTX = 3;
const DATE_LASTX_HOUR = 1, DATE_LASTX_12HOURS = 2, DATE_LASTX_24HOURS = 3, DATE_LASTX_7DAYS = 4,DATE_LASTX_31DAYS = 5;

/*
Helper Javascript functions
*/

/*
*	Helper function to show and hide areas of the filterview
*/
function toggleDatefiltervisibility(FormName)
{
	var myform = document.getElementById(FormName);
	if (myform.elements['filter_datemode'].value == DATEMODE_ALL)
	{
		hidevisibility('HiddenDateFromOptions');
		hidevisibility('HiddenDateLastXOptions');
	}
	else if (myform.elements['filter_datemode'].value == DATEMODE_RANGE)
	{
		togglevisibility('HiddenDateFromOptions');
		hidevisibility('HiddenDateLastXOptions');
	}
	else if (myform.elements['filter_datemode'].value == DATEMODE_LASTX)
	{
		togglevisibility('HiddenDateLastXOptions');
		hidevisibility('HiddenDateFromOptions');
	}

}

/*
*	Helper function to add a date filter into the search field
*/
function CalculateSearchPreview(szSearchFormName, szPreviewArea)
{
	var mySearchform = document.getElementById(szSearchFormName);
	var myPreviewArea = document.getElementById(szPreviewArea);
	var szOutString = "", szTmpString = "", nCount = 0;
	if (mySearchform.elements['filter_datemode'].value == DATEMODE_RANGE)
	{
		szOutString += "datefrom:"	+ mySearchform.elements['filter_daterange_from_year'].value + "-" 
									+ mySearchform.elements['filter_daterange_from_month'].value + "-"
									+ mySearchform.elements['filter_daterange_from_day'].value + "T00:00:00 ";
		szOutString += "dateto:"	+ mySearchform.elements['filter_daterange_to_year'].value + "-" 
									+ mySearchform.elements['filter_daterange_to_month'].value + "-"
									+ mySearchform.elements['filter_daterange_to_day'].value + "T00:00:00 ";
	}
	else if (mySearchform.elements['filter_datemode'].value == DATEMODE_LASTX)
	{
		szOutString += "datelastx:" + mySearchform.elements['filter_daterange_last_x'].value + " ";
	}

	// --- Syslog Facility
	szTmpString = "";
	nCount = 0;
	for (var i = 0; i < mySearchform.elements['filter_facility[]'].length; i++)
	{
		if (mySearchform.elements['filter_facility[]'].options[i].selected == true)
		{
			if ( szTmpString.length > 0)
			{
				szTmpString += ",";
			}
			szTmpString += mySearchform.elements['filter_facility[]'].options[i].value;
			nCount++;
		}
	}
	if ( nCount < 18 )
	{	
		// Only if not all selected!
		szOutString += "facility:" + szTmpString + " ";
	}
	// --- 

	// --- Syslog Severity
	szTmpString = "";
	nCount = 0;
	for (var i = 0; i < mySearchform.elements['filter_severity[]'].length; i++)
	{
		if (mySearchform.elements['filter_severity[]'].options[i].selected == true)
		{
			if ( szTmpString.length > 0)
			{
				szTmpString += ",";
			}
			szTmpString += mySearchform.elements['filter_severity[]'].options[i].value;
			nCount++;
		}
	}
	if ( nCount < 8 )
	{	
		// Only if not all selected!
		szOutString += "severity:" + szTmpString + " ";
	}
	// --- 

	// --- SyslogTag
	if (mySearchform.elements['filter_syslogtag'].value.length > 0 )
	{
		szOutString += "syslogtag:" + mySearchform.elements['filter_syslogtag'].value + " ";
	}
	// --- 

	// --- Source
	if (mySearchform.elements['filter_source'].value.length > 0 )
	{
		szOutString += "source:" + mySearchform.elements['filter_source'].value + " ";
	}
	// --- 

	// --- Message | Just append as it is 
	szOutString += mySearchform.elements['filter_message'].value; 
	// --- 

	// Set preview area
	myPreviewArea.innerHTML = szOutString;
}