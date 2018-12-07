<?php 
set_time_limit(936000);
ini_set('max_execution_time', 936000);
ini_set('memory_limit', '900M');

if(
	isset($_REQUEST['ruta_log'])
	&& isset($_REQUEST['accion'])
	&& trim($_REQUEST['ruta_log'])!=""
	&& trim($_REQUEST['accion'])!=""
	&& trim($_REQUEST['accion'])=="ultima"
)//fin if condicion
{
	$ruta_log=trim($_REQUEST['ruta_log']);
	
	if(file_exists($ruta_log)==true)
	{
		//CODE FROM Ionuț G. Stan on stackoverflow
		$line = '';

		$f = fopen($ruta_log, 'r');
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") 
		{
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}//fin while 

		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") 
		{
		    /**
		     * Prepend the new char
		     */
		    $line = $char . $line;
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}//fin while 

		echo "L: ".$line;
		//FIN CODE FROM Ionuț G. Stan on stackoverflow
	}//fin if
	else
	{
		//echo "Log no creado aun.";
	}//fin else
	
}//fin if
?>