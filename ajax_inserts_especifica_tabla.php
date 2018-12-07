<?php
set_time_limit(936000);
ini_set('max_execution_time', 936000);
ini_set('memory_limit', '900M');
ini_set('log_errors', 1);
//ini_set('error_log', 'error_log_inserciones_especifico_bd.log');
require_once('clase_conexion_bd.php');
require_once('czip.php');

$ruta_zip="";
$string_ruta="";
$ruta_log_error_sin_dupl="";
$array_ruta=array();
$array_ruta2=array();
$array_ruta3=array();
if(isset($_REQUEST['ruta_archivo_inserts']) && trim($_REQUEST['ruta_archivo_inserts'])!="" )
{
	$array_ruta=explode("/", trim($_REQUEST['ruta_archivo_inserts']) );
	$array_ruta2=$array_ruta;
	$array_ruta3=$array_ruta;
	$array_ruta[count($array_ruta)-1]='error_log_inserciones_especifico_bd.log';
	$array_ruta2[count($array_ruta2)-1]='error_log_inserciones_especifico_bd.zip';
	$array_ruta3[count($array_ruta3)-1]='error_log_inserciones_excl_errores_dupl_bd.log';
	$string_ruta=implode("/", $array_ruta);
	$ruta_zip=implode("/", $array_ruta2);	
	$ruta_log_error_sin_dupl=implode("/", $array_ruta3);

	//inicualizacion 
	ini_set('error_log', $string_ruta);
	$handler_errores_excl_dupl=fopen($ruta_log_error_sin_dupl, 'a');
	fclose($handler_errores_excl_dupl);
}//fin if
else
{
	ini_set('error_log', 'error_log_inserciones_especifico_bd.log');
}

function contar_numero_lineas($ruta_file)
{
	$linecount = 0;
	$handle = fopen($ruta_file, "r");
	while(!feof($handle))
	{
	  $line = fgets($handle);
	  $linecount++;
	}//fin while
	fclose($handle);
	
	return $linecount;
}//fin function

//ruta_archivo_inserts,servidor_contrario,usuario_contrario,contrasena_contrario, base_de_datos_contrario, puerto_contrario
if(isset($_REQUEST['ruta_archivo_inserts'])
&& isset($_REQUEST['servidor_contrario'])
&& isset($_REQUEST['usuario_contrario'])
&& isset($_REQUEST['contrasena_contrario'])
&& isset($_REQUEST['base_de_datos_contrario'])
&& isset($_REQUEST['puerto_contrario'])
&& trim($_REQUEST['ruta_archivo_inserts'])!=""
&& trim($_REQUEST['servidor_contrario'])!=""
&& trim($_REQUEST['usuario_contrario'])!=""
&& trim($_REQUEST['contrasena_contrario'])!=""
&& trim($_REQUEST['base_de_datos_contrario'])!=""
&& trim($_REQUEST['puerto_contrario'])!=""
)
{
	$ruta_archivo_inserts=trim($_REQUEST['ruta_archivo_inserts']);
	$servidor_contrario=trim($_REQUEST['servidor_contrario']);
	$usuario_contrario=trim($_REQUEST['usuario_contrario']);
	$contrasena_contrario=trim($_REQUEST['contrasena_contrario']);
	$base_de_datos_contrario=trim($_REQUEST['base_de_datos_contrario']);
	$puerto_contrario=trim($_REQUEST['puerto_contrario']);

	$conexion_para_inserts= new clase_conexion_bd($servidor_contrario,$usuario_contrario,$contrasena_contrario,$base_de_datos_contrario,$puerto_contrario);

	$conexion_para_inserts->crear_conexion();

	$numero_lineas_archivo_insert=contar_numero_lineas($ruta_archivo_inserts);

	$archivo_inserts_lectura=fopen($ruta_archivo_inserts, 'r');
	$cont=0;
	$cont_erroneos=0;
	$cont_exitosos=0;
	$numero_reconecciones=0;

	//aca solo se lee el insert, el problema seria donde se genera

	while(!feof($archivo_inserts_lectura))
	{
	  $linea_con_insert = trim(fgets($archivo_inserts_lectura));

	  $es_un_insert=strpos($linea_con_insert, "INSERT");

	  if($es_un_insert!==false && intval($es_un_insert)>=0 )
	  {
	  	$error_bd="";
	  	$resultado_bool=true;
	  	$estado_conn="conectado";
		do
		{
			if($estado_conn=="fallida" )
			{
				error_log("conn inserts reconectando para query ".$linea_con_insert);
				$conexion_para_inserts->reconectar();
				$numero_reconecciones++;
			}
		  	$resultado_bool=$conexion_para_inserts->insertar($linea_con_insert,$error_bd);
		  	if( $error_bd!="")
			{
				$estado_conn=$conexion_para_inserts->estado_conexion();
			}
	  	}while($estado_conn=="fallida");
	  	if($error_bd!="" || $resultado_bool===false)
	  	{
	  		$mensaje_error_para_log="";
	  		$mensaje_error_para_log.=$cont." No se pudo realizar insert de ".$linea_con_insert.", detalle error bd: ".$error_bd;
	  		error_log();

	  		$resultado_int_preg_match=preg_match('/\b(?:(?!exists)\w)+\b[\.]/', $error_bd);
	  		if($resultado_int_preg_match==1)
	  		{
	  			$handler_errores_excl_dupl=fopen($ruta_log_error_sin_dupl, 'a');
	  			fwrite($handler_errores_excl_dupl, $mensaje_error_para_log);
	  			fclose($handler_errores_excl_dupl);
	  		}//fin if
	  		$cont_erroneos++;
	  	}//fin if
	  	else
	  	{
	  		error_log($cont." Se realizo insert de ".$linea_con_insert);
	  		$cont_exitosos++;
	  	}//fin else

	  }//fin if

	  $cont++;
	}//fin while
	fclose($archivo_inserts_lectura);

	$conexion_para_inserts->cerrar_conexion();

	makeZip(array($string_ruta,$ruta_log_error_sin_dupl),$ruta_zip,true);

	echo "Se realizo la insercion exitosa de ($cont_exitosos) registros, con ($cont_erroneos) de registros fallidos. Total de registros de inserciones encontrados  (".($cont_exitosos+$cont_erroneos).")... Numero Reconecciones Realizadas $numero_reconecciones ... <a href='$ruta_zip' target='blank_'>log inserciones</a> ";

}//fin if isset 
?>