<?php
set_time_limit(936000);
ini_set('max_execution_time', 936000);
ini_set('memory_limit', '900M');
ini_set('log_errors', 1);
//ini_set('error_log', 'error_log_comparaciones_bd.log');
require_once('clase_conexion_bd.php');

require_once('gui_selectores_bd.php');

function string_de_array_como_descripcion($param_array)
{
	$resultado="";

	$array_temp=array();
	$indices_array=array_keys($param_array);

	foreach ($indices_array as $key => $indice) 
	{
		if(is_array($param_array[$indice])==false)
		{
			$array_temp[]=$indice.": ".$param_array[$indice];
		}//fin if
	}//fin foreach
	
	$resultado=implode(", ", $array_temp);

	return $resultado;

}//fin function

//$user_agent = $_SERVER['HTTP_USER_AGENT'];
$user_agent = php_uname();
if(strpos(strtolower($user_agent), "windows")!==false)
{
  $user_agent = "windows nt 10";
}//fin if

function getOS() 
{ 

    global $user_agent;

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
                          '/windows nt 10/i'      =>  'Windows 10',
                          '/windows nt 6.3/i'     =>  'Windows 8.1',
                          '/windows nt 6.2/i'     =>  'Windows 8',
                          '/windows nt 6.1/i'     =>  'Windows 7',
                          '/windows nt 6.0/i'     =>  'Windows Vista',
                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                          '/windows nt 5.1/i'     =>  'Windows XP',
                          '/windows xp/i'         =>  'Windows XP',
                          '/windows nt 5.0/i'     =>  'Windows 2000',
                          '/windows me/i'         =>  'Windows ME',
                          '/win98/i'              =>  'Windows 98',
                          '/win95/i'              =>  'Windows 95',
                          '/win16/i'              =>  'Windows 3.11',
                          '/macintosh|mac os x/i' =>  'Mac OS X',
                          '/mac_powerpc/i'        =>  'Mac OS 9',
                          '/linux/i'              =>  'Linux',
                          '/ubuntu/i'             =>  'Ubuntu',
                          '/iphone/i'             =>  'iPhone',
                          '/ipod/i'               =>  'iPod',
                          '/ipad/i'               =>  'iPad',
                          '/android/i'            =>  'Android',
                          '/blackberry/i'         =>  'BlackBerry',
                          '/webos/i'              =>  'Mobile'
                    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}//fin function


$user_os = getOS();

$ruta_global_funciones_postgres_bin_linux="/lappstack-5.6.32-1/postgresql/bin/";
$ruta_global_funciones_postgres_bin_windows="/lappstack-5.6.32-1/postgresql/bin/";

//echo "<h5 style='text-align:center;'>El archivo de las diferencias totales encontradas al realizar la comparacion no incluyen la diferencia entre propietarios de tablas, estas estaran en su archivo aparte correspondiente<br><h5>";

/*
//tabla prueba comparacion, es la misma pero con diferentes columnas y restricciones
create table test_comp
(
	column_1 int,
	descripcion character varying(320),
	primary key(column_1)
);

create table test_comp2
(
	column_1 int,
	descripcion character varying(320),
	primary key(column_1)
);

create table test_comp
(
	column_1 int,
	descripcion character varying(50),
	descripcion_2 character varying(320)
);
*/
$submit_signal="";
if(isset($_REQUEST['submit_signal'])==true && trim($_REQUEST['submit_signal'])!="")
{
	$submit_signal=trim($_REQUEST['submit_signal']);
}//fin if

$html_resumen_estado="";

date_default_timezone_set ("America/Bogota");
$fecha_actual = date('Y-m-d');
$tiempo_actual = date('H:i:s');
$tiempo_actual_string=str_replace(":","-",$tiempo_actual);

$fecha_para_archivo=str_replace("-", "", $fecha_actual ).str_replace(":", "", $tiempo_actual );

//DIRECTORIO DE LOS ARCHIVOS
$ruta_destino="resultados_comp/";
if(file_exists($ruta_destino)==true && is_writable($ruta_destino)==false)
{
	chmod($ruta_destino, 0777);
}//fin if
$nueva_carpeta=$ruta_destino."comparacionesbd".$fecha_para_archivo;
if(!file_exists($nueva_carpeta) && $submit_signal=='verificado')
{
    mkdir($nueva_carpeta, 0777, true);
}
else
{
    $files_to_erase = glob($nueva_carpeta."/*"); // get all file names
    foreach($files_to_erase as $file_to_be_erased)
    { // iterate files
      if(is_file($file_to_be_erased))
      {
		unlink($file_to_be_erased); // delete file
      }
    }
}//fin else
$ruta_carpeta_consultas_guardadas="consultas_almacenadas";
if(!file_exists($ruta_carpeta_consultas_guardadas))
{
    mkdir($ruta_carpeta_consultas_guardadas, 0777, true);
}//fin if no borra nada solo la crea si no existe
$ruta_destino_consultas_guardadas=$ruta_carpeta_consultas_guardadas."/";
$ruta_destino=$nueva_carpeta."/";
//FIN DIRECTORIO DE LOS ARCHIVOS
$ruta_log1=$ruta_destino.'error_log_comparaciones_bd.log';
ini_set('error_log', $ruta_log1);
//echo print_r($_REQUEST,true)."<br>";


$matriz_datos_conexion=array();

$array_conexiones=array();

$matriz_tablas_por_conexion=array();

$matriz_secuencias_por_conexion=array();

$matriz_funciones_en_bd_por_conexion=array();

$matriz_triggers_por_conexion=array();

$cont_request=1;
$almacenar_consulta=true;
$html_estado_consulta_inicial="";
$cantidad_conexiones=0;

$html_estado_consulta_inicial.=$user_os."<br>";

$nombre_a_guardar_conf_conexion="";
if(isset($_REQUEST['nombre_a_guardar_consulta_conexiones'])
&& trim($_REQUEST['nombre_a_guardar_consulta_conexiones'])!="" 
)
{
	$nombre_a_guardar_conf_conexion=str_replace(" ", "_",trim($_REQUEST['nombre_a_guardar_consulta_conexiones']) );
 	echo "<script>	document.getElementById('nombre_a_guardar_consulta_conexiones').value='".$nombre_a_guardar_conf_conexion."';	</script>";
}//fin fi

$limites_comparacion=0;
if(isset($_REQUEST['limites_comparacion'])
&& trim($_REQUEST['limites_comparacion'])!="" 
)
{
	$limites_comparacion=intval(trim($_REQUEST['limites_comparacion']));
}//fin if

$generar_sql_estructuras=0;
if(isset($_REQUEST['generar_sql_estructuras'])
&& trim($_REQUEST['generar_sql_estructuras'])!="" 
)
{
	$generar_sql_estructuras=intval(trim($_REQUEST['generar_sql_estructuras']));
}//fin if

$limites_comparacion_string_msg="Estructura";
if($limites_comparacion==1)
{
	$limites_comparacion_string_msg="Datos y Estructura";
}//fin if
$html_estado_consulta_inicial.="Se procedera a comparar (".$limites_comparacion_string_msg.") entre las bases de datos.<br>";

$generar_sql_estructuras_string_msg="NINGUNA";
if($generar_sql_estructuras==1)
{
	$generar_sql_estructuras_string_msg="Solo Estructura";
}//fin if
else if($generar_sql_estructuras==2)
{
	$generar_sql_estructuras_string_msg="Datos y Estructura";
}//fin if
$html_estado_consulta_inicial.="Se procedera a generar el SQL de (".$generar_sql_estructuras_string_msg.") correspondientes a las tablas de la base de datos donde existen.<br>";


//PARAMETROS CONEXION DE LAS BASES DE DATOS A COMPARAR
while(isset($_REQUEST['servidor_bd'.$cont_request])
	&& isset($_REQUEST['usuario_bd'.$cont_request])
	&& isset($_REQUEST['contrasena_bd'.$cont_request])
	&& isset($_REQUEST['base_de_datos_bd'.$cont_request])
	&& isset($_REQUEST['puerto_bd'.$cont_request])
	)//fin condicion
{
	$SERVIDOR=trim($_REQUEST['servidor_bd'.$cont_request]);
	$USUARIO=trim($_REQUEST['usuario_bd'.$cont_request]);
	$CONTRASENA=trim($_REQUEST['contrasena_bd'.$cont_request]);
	$BASE_DE_DATOS=trim($_REQUEST['base_de_datos_bd'.$cont_request]);
	$PUERTO=trim($_REQUEST['puerto_bd'.$cont_request]);
	$matriz_datos_conexion[]=array("SERVIDOR"=>$SERVIDOR,"USUARIO"=>$USUARIO,"CONTRASENA"=>$CONTRASENA,"BASE_DE_DATOS"=>$BASE_DE_DATOS,"PUERTO"=>$PUERTO);



	$html_re_escribe_parametros="";
	$html_re_escribe_parametros.="
	<script>
	document.getElementById('servidor_bd".$cont_request."').value='".$SERVIDOR."';
	document.getElementById('usuario_bd".$cont_request."').value='".$USUARIO."';
	document.getElementById('contrasena_bd".$cont_request."').value='".$CONTRASENA."';
	document.getElementById('base_de_datos_bd".$cont_request."').value='".$BASE_DE_DATOS."';
	document.getElementById('puerto_bd".$cont_request."').value='".$PUERTO."';
	</script>";
	echo $html_re_escribe_parametros;

	$cantidad_conexiones=count($matriz_datos_conexion);

	if($cantidad_conexiones>=2)
	{
		//llamarCantidadEspecifica('cantidad_especifica_fin');
		$cantidad_conexiones_plus_one=$cantidad_conexiones+1;
		$cont_request_plus_one=$cont_request+1;

		$html_cantidad_conexiones="";

		if(isset($_REQUEST['servidor_bd'.$cont_request_plus_one])
		&& isset($_REQUEST['usuario_bd'.$cont_request_plus_one])
		&& isset($_REQUEST['contrasena_bd'.$cont_request_plus_one])
		&& isset($_REQUEST['base_de_datos_bd'.$cont_request_plus_one])
		&& isset($_REQUEST['puerto_bd'.$cont_request_plus_one])
		)//fin condicion
		{		
			$html_cantidad_conexiones.="
			<script>
			document.getElementById('cantidad_especifica_ini').value='$cantidad_conexiones_plus_one';
			document.getElementById('cantidad_especifica_fin').value='$cantidad_conexiones_plus_one';
			llamarCantidadEspecifica('cantidad_especifica_fin');
			</script>
			";
		}//fin if
		else
		{
			$html_cantidad_conexiones.="
			<script>
			document.getElementById('cantidad_especifica_ini').value='$cantidad_conexiones';
			document.getElementById('cantidad_especifica_fin').value='$cantidad_conexiones';
			llamarCantidadEspecifica('cantidad_especifica_fin');
			</script>
			";
		}//fin else
		echo $html_cantidad_conexiones;
	}//fin if mayor o igual a 2 conexiones


	//echo "Conexion Numero: $cont_request , Parametros Conexion Actual: ".print_r($matriz_datos_conexion[count($matriz_datos_conexion)-1],true)."<br>";
	ob_flush();
	flush();

	$cont_request++;
}//fin while
//FIN PARAMETROS CONEXION DE LAS BASES DE DATOS A COMPARAR

echo "<script>var intervaloLog=setInterval(function(){ mostrar_ultima_linea_log('$ruta_log1','div_visor1'); }, 1000);</script>";



//CONSULTA INFO BASES DE DATOS
$contador_conexiones_bd=0;
while($contador_conexiones_bd<count($matriz_datos_conexion) )
{
	$verificador_conexion_actual=true;

	$reflection_class = new ReflectionClass('clase_conexion_bd');
	$array_conexiones[$contador_conexiones_bd] = $reflection_class->newInstanceArgs( $matriz_datos_conexion[$contador_conexiones_bd] );
	$array_conexiones[$contador_conexiones_bd]->crear_conexion();

	$query_listado_tablas_bd_conexion_actual="";
	$query_listado_tablas_bd_conexion_actual.="
		SELECT * FROM pg_tables WHERE schemaname='public'
	";

	$nombre_base_de_datos=$matriz_datos_conexion[$contador_conexiones_bd]["BASE_DE_DATOS"];	

	/*
	//esta query trae todas las tablas pero no se peude saber las de la ase de datos actual por ahora
	$query_listado_tablas_bd_conexion_actual.="
		SELECT c.oid as id, c.relname as nombre_tabla, c.*
	    FROM pg_catalog.pg_class c 
	    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace where n.nspname='public'
	";
	*/

	$error="";
	$resultado_listado_tablas_conexion_actual=$array_conexiones[$contador_conexiones_bd]->consultar($query_listado_tablas_bd_conexion_actual,$error);
	if(count($resultado_listado_tablas_conexion_actual)==0 || !is_array($resultado_listado_tablas_conexion_actual) )
	{
		$error_procesado=str_replace("'", "|",  trim($error) );
		$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
		$html_resumen_estado.= "<br>Hubo un error al consultar las tablas de la conexion actual.<br>$error_procesado<br>";
		$almacenar_consulta=false;
		$verificador_conexion_actual=false;
	}
	else if(count($resultado_listado_tablas_conexion_actual)>=1 && is_array($resultado_listado_tablas_conexion_actual) )
	{
		error_log("Se consultaron las tablas de la conexion $contador_conexiones_bd ");
		
		//$array_en_cadena=print_r($resultado_listado_tablas_conexion_actual,true);
		//echo "<br>$array_en_cadena<br>";

		$matriz_tablas_por_conexion[$contador_conexiones_bd]=$resultado_listado_tablas_conexion_actual;
		
	}//fin else if

	$contador_tabla_actual=0;
	while($contador_tabla_actual<count($matriz_tablas_por_conexion[$contador_conexiones_bd]) )
	{
	
		//PARTE COLUMNAS TABLA ACTUAL
		$nombre_tabla_actual="";
		$nombre_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["tablename"];

		//echo "<br>Base de Datos: $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), Posicion: $contador_tabla_actual , Nombre tabla: $nombre_tabla_actual .<br>";
		$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["columnas_info"]=array();
		$query_info_columnas_tabla="";
		$query_info_columnas_tabla.="
		SELECT
		  a.attname as column,
		  pg_catalog.format_type(a.atttypid, a.atttypmod) as datatype

		  FROM
		  pg_catalog.pg_attribute a
		  WHERE
		    a.attnum > 0
		  AND NOT a.attisdropped
		  AND a.attrelid = (
		    SELECT c.oid
		    FROM pg_catalog.pg_class c
		    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
		    WHERE c.relname ~ '^(".$nombre_tabla_actual.")\$'
		   AND pg_catalog.pg_table_is_visible(c.oid)
		  )
		  ;
		";//para probar en postgres remover el backslash al \$ para que quede $ y cambiar ".$nombre_tabla_actual." por el nomrbe especifico

		//echo "<br>$query_info_columnas_tabla<br>";

		$error="";
		$resultado_columnas_tabla_actual=$array_conexiones[$contador_conexiones_bd]->consultar($query_info_columnas_tabla,$error);
		if(count($resultado_columnas_tabla_actual)==0 || !is_array($resultado_columnas_tabla_actual) )
		{
			$error_procesado=str_replace("'", "|",  trim($error) );
			$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
			$html_resumen_estado.= "<br>Hubo un error al consultar las columnas de la tabla actual $nombre_tabla_actual en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>$error_procesado<br>";
		}
		else if(count($resultado_columnas_tabla_actual)>=1 && is_array($resultado_columnas_tabla_actual) )
		{
			error_log("Se consultaron las columnas de la tabla $nombre_tabla_actual de la conexion $contador_conexiones_bd ");
			//$array_en_cadena=print_r($resultado_columnas_tabla_actual,true);
			//echo "<br>$array_en_cadena<br>";
			$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["columnas_info"]=$resultado_columnas_tabla_actual;
		}//fin else if
		//FIN PARTE COLUMNAS TABLA ACTUAL

		//RESTRICCIONES TABLA ACTUAL
		$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["restricciones_info"]=array();
		$query_restricciones_tabla="
		SELECT c.relname, cst.* from pg_catalog.pg_constraint cst 
		LEFT JOIN (pg_catalog.pg_class c 
		LEFT JOIN pg_tables pt 
		ON c.relname=pt.tablename)  
		ON c.oid=cst.conrelid  
		WHERE c.relname ~ '^(".$nombre_tabla_actual.")\$' ;
		";//para probar en postgres remover el backslash al \$ para que quede $ y cambiar ".$nombre_tabla_actual." por el nomrbe especifico

		$error="";
		$resultado_restricciones_tabla_actual=$array_conexiones[$contador_conexiones_bd]->consultar($query_restricciones_tabla,$error);
		if(count($resultado_restricciones_tabla_actual)==0 || !is_array($resultado_restricciones_tabla_actual) )
		{
			if(trim($error)!="")
			{
				$error_procesado=str_replace("'", "|",  trim($error) );
				$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
				$html_resumen_estado.= "<br>Hubo un error al consultar las restricciones de la tabla actual $nombre_tabla_actual en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>$error_procesado<br>";
			}//fin if
			else
			{
				$html_resumen_estado.= "<br>No tiene restricciones la tabla actual $nombre_tabla_actual en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>";
			}
		}
		else if(count($resultado_restricciones_tabla_actual)>=1 && is_array($resultado_restricciones_tabla_actual) )
		{
			error_log("Se consultaron las restricciones de la tabla $nombre_tabla_actual de la conexion $contador_conexiones_bd ");
			//$array_en_cadena=print_r($resultado_restricciones_tabla_actual,true);
			//echo "<br>$array_en_cadena<br>";
			$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["restricciones_info"]=$resultado_restricciones_tabla_actual;
		}//fin else if
		//FIN RESTRICCIONES TABLA ACTUAL

		if($nombre_tabla_actual=="test_comp")
		{
			//echo "DAVELORD Tabla $nombre_tabla_actual, Columnas:  ".print_r($resultado_columnas_tabla_actual,true).", Restricciones:  ".print_r($resultado_restricciones_tabla_actual,true);
		}

		$numero_registro_tabla=0;
		if($limites_comparacion==1)
		{
			$query_consultar_numero_registros="SELECT count(*) as numero_registros FROM ".$nombre_tabla_actual." ; ";
			$error_bd="";
			$resultado_numero_registros_tabla=$array_conexiones[$contador_conexiones_bd]->consultar($query_consultar_numero_registros,$error_bd);
			if(count($resultado_numero_registros_tabla)>0 && is_array($resultado_numero_registros_tabla) )
			{
				$numero_registro_tabla=intval($resultado_numero_registros_tabla[0]['numero_registros']);
				$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["numero_registros_info"]=$numero_registro_tabla;

				error_log("Se consulto la siguiente cantidad de registros ($numero_registro_tabla) para la tabla $nombre_tabla_actual de la conexion $contador_conexiones_bd ");
			}//fin if
			else
			{
				$error_procesado=str_replace("'", "|",  trim($error_bd) );
				$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
				$html_resumen_estado.= "Error: ($error_procesado) query ($query_consultar_numero_registros).<br>";
			}//fin else
		}//fin if 
		else
		{
			$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["numero_registros_info"]=$numero_registro_tabla;
			error_log("Se omite la consulta de registros de la tabla actual ($numero_registro_tabla) debido a que se selecciono estructura  ");
		}//fin if



		$contador_tabla_actual++;
	}//fin while tablas

	//LISTAR SECUENCIAS DE LA BASE DE DATOS ACTUAL
	$query_secuencias="";
	$query_secuencias.="SELECT c.relname FROM pg_class c WHERE c.relkind = 'S'; ";
	$error="";
	$resultado_secuencias=$array_conexiones[$contador_conexiones_bd]->consultar($query_secuencias,$error);
	if(count($resultado_secuencias)==0 || !is_array($resultado_secuencias) )
	{
		$error_procesado=str_replace("'", "|",  trim($error) );
		$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
		$html_resumen_estado.= "<br>Hubo un error al consultar las secuencias en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>$error_procesado<br>";
	}
	else if(count($resultado_secuencias)>=1 && is_array($resultado_secuencias) )
	{
		error_log("Se consulto las secuencias de la conexion $contador_conexiones_bd ");
		$matriz_secuencias_por_conexion[$contador_conexiones_bd]['nombres_secuencias']=$resultado_secuencias;
	}//fin else if

	//LISTAR FUNCIONES
	$query_funciones_en_bd="";
	$query_funciones_en_bd.="
		SELECT routines.routine_name, parameters.data_type, parameters.ordinal_position
		FROM information_schema.routines
		    LEFT JOIN information_schema.parameters ON routines.specific_name=parameters.specific_name
		WHERE routines.specific_schema='public'
		ORDER BY routines.routine_name, parameters.ordinal_position;
	";
	$error="";
	$resultado_funciones_en_bd=$array_conexiones[$contador_conexiones_bd]->consultar($query_funciones_en_bd,$error);
	if(count($resultado_funciones_en_bd)==0 || !is_array($resultado_funciones_en_bd) )
	{
		$error_procesado=str_replace("'", "|",  trim($error) );
		$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
		$html_resumen_estado.= "<br>Hubo un error al consultar las funciones en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>$error_procesado<br>";
	}
	else if(count($resultado_funciones_en_bd)>=1 && is_array($resultado_funciones_en_bd) )
	{
		error_log("Se consulto las funciones de la conexion $contador_conexiones_bd ");
		$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones']=$resultado_funciones_en_bd;
	}//fin else if


	//LISTAR TRIGGERS
	$query_listar_triggers="";
	$query_listar_triggers.="
	select trg.tgname,
        CASE trg.tgtype::integer & 66
            WHEN 2 THEN 'BEFORE'
            WHEN 64 THEN 'INSTEAD OF'
            ELSE 'AFTER'
        end as trigger_type,
       case trg.tgtype::integer & cast(28 as int2)
         when 16 then 'UPDATE'
         when 8 then 'DELETE'
         when 4 then 'INSERT'
         when 20 then 'INSERT, UPDATE'
         when 28 then 'INSERT, UPDATE, DELETE'
         when 24 then 'UPDATE, DELETE'
         when 12 then 'INSERT, DELETE'
       end as trigger_event,
       ns.nspname||'.'||tbl.relname as trigger_table,
       obj_description(trg.oid) as remarks,
         case
          when trg.tgenabled='O' then 'ENABLED'
            else 'DISABLED'
        end as status,
        case trg.tgtype::integer & 1
          when 1 then 'ROW'::text
          else 'STATEMENT'::text
        end as trigger_level
FROM pg_trigger trg
 JOIN pg_class tbl on trg.tgrelid = tbl.oid
 JOIN pg_namespace ns ON ns.oid = tbl.relnamespace
WHERE trg.tgname not like 'RI_ConstraintTrigger%'
  AND trg.tgname not like 'pg_sync_pg%'
	";

	$error="";
	$resultado_triggers=$array_conexiones[$contador_conexiones_bd]->consultar($query_listar_triggers,$error);
	if(count($resultado_triggers)==0 || !is_array($resultado_triggers) )
	{
		if($error!="")
		{
			$error_procesado=str_replace("'", "|",  trim($error) );
			$error_procesado=str_replace("\"", "|",  trim($error_procesado) );
			$html_resumen_estado.= "<br>Hubo un error al consultar los triggers en la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd).<br>$error_procesado<br>";
		}//fin if
		else
		{
			$html_resumen_estado.= "<br>No Encontro Triggers.<br>";
		}//fin else
		
	}
	else if(count($resultado_triggers)>=1 && is_array($resultado_triggers) )
	{
		error_log("Se consulto los triggers de la conexion $contador_conexiones_bd ");
		$matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers']=$resultado_triggers;
	}//fin else if



	$array_conexiones[$contador_conexiones_bd]->cerrar_conexion();

	if($verificador_conexion_actual==true)
	{
		$html_estado_consulta_inicial.="Se pudo realizar la consulta inicial de las tablas pertenecientes a la conexion con los siguientes datos: ".string_de_array_como_descripcion($matriz_datos_conexion[$contador_conexiones_bd])."<br>";
		echo "<script>document.getElementById('div_respuesta').innerHTML=\"$html_estado_consulta_inicial\";</script>";
		ob_flush();
		flush();
	}//fin if se consultaron tbalas de dicha conexion
	else
	{
		$html_estado_consulta_inicial.="NO Se pudo realizar la consulta inicial de las tablas pertenecientes, VERIFIQUE si los siguientes datos de la conexion son correctos: ".string_de_array_como_descripcion($matriz_datos_conexion[$contador_conexiones_bd])."<br>";
		echo "<script>document.getElementById('div_respuesta').innerHTML=\"$html_estado_consulta_inicial\";</script>";
		ob_flush();
		flush();
	}//fin else

	$contador_conexiones_bd++;
}//fin while conexiones
//FIN CONSULTA INFO BASES DE DATOS

echo "<script>clearInterval(intervaloLog); mostrar_ultima_linea_log('$ruta_log1','div_visor1'); </script>";

$ruta_log2=$ruta_destino."log_comp_entre_tablas.log";
echo "<script>var intervaloLog2=setInterval(function(){ mostrar_ultima_linea_log('$ruta_log2','div_visor2'); }, 5000);</script>";

$ruta_log3=$ruta_destino."error_log_inserciones_especifico_bd.log";
echo "<script>var intervaloLog3=setInterval(function(){ mostrar_ultima_linea_log('$ruta_log3','div_visor3'); }, 7000);</script>";




//PARTE GUARDA CONSULTA SI NO HUBO PROBLEMAS
if($almacenar_consulta==true && count($matriz_datos_conexion)>0)
{
	$cantidad_conexiones=count($matriz_datos_conexion);

	$code_bds_comparadas="";
	foreach ($matriz_datos_conexion as $key => $conexion_actual) 
	{
		$primeras_6_letras=substr($conexion_actual['BASE_DE_DATOS'], 0, 6);
		$code_bds_comparadas.=$primeras_6_letras;
	}//fin foreach

	$ruta_archivo_consulta_mas_reciente="";
	if($nombre_a_guardar_conf_conexion!="")
	{
		$ruta_archivo_consulta_mas_reciente=$ruta_destino_consultas_guardadas.$nombre_a_guardar_conf_conexion.".csv";
	}//fin if
	else
	{
		$ruta_archivo_consulta_mas_reciente=$ruta_destino_consultas_guardadas."consulta_de_n".$cantidad_conexiones."_".$code_bds_comparadas.".csv";
	}//fin else

	$file_archivo_consulta_mas_reciente = fopen($ruta_archivo_consulta_mas_reciente, "w") or die("fallo la creacion del archivo");
	$titulo="SEP=|\nconsulta_de_n".$cantidad_conexiones." fecha y hora: ".$fecha_para_archivo;
	fwrite($file_archivo_consulta_mas_reciente,$titulo);

	foreach ($matriz_datos_conexion as $key => $conexion_actual) 
	{
		$SERVIDOR=$conexion_actual['SERVIDOR'];
		$USUARIO=$conexion_actual['USUARIO'];
		$CONTRASENA=$conexion_actual['CONTRASENA'];
		$BASE_DE_DATOS=$conexion_actual['BASE_DE_DATOS'];
		$PUERTO=$conexion_actual['PUERTO'];

		$linea_conexion=$SERVIDOR."|".$USUARIO."|".$CONTRASENA."|".$BASE_DE_DATOS."|".$PUERTO;
		fwrite($file_archivo_consulta_mas_reciente,"\n".$linea_conexion);
		

	}//fin foreach
	fclose($file_archivo_consulta_mas_reciente);
}//fin if
//FIN PARTE GUARDA CONSULTA SI NO HUBO PROBLEMAS

//definicion de variables que almacenan las rutas de los archivos
$ruta_archivo_diferencias_totales="";
$ruta_archivo_equivalencias="";
$ruta_archivo_diferencias_triggers="";
$ruta_archivo_diferencias_secuencias="";
$ruta_archivo_diferencias_funciones_en_bd="";
$ruta_archivo_diferencias_tablas="";
$ruta_archivo_diferencias_col="";
$ruta_archivo_diferencias_rest="";
$ruta_archivo_diferencias_prop="";

$array_rutas_archivos_equivalencias=array();
$array_rutas_archivos_diferencias_triggers=array();
$array_rutas_archivos_diferencias_secuencias=array();
$array_rutas_archivos_diferencias_funciones_en_bd=array();
$array_rutas_archivos_diferencias_tablas=array();
$array_rutas_archivos_diferencias_col=array();
$array_rutas_archivos_diferencias_rest=array();
$array_rutas_archivos_diferencias_prop=array();

$array_rutas_archivos_alters_col=array();
$array_rutas_dumps_tablas=array();
$array_rutas_dumps_sequences=array();

$encontro_tablas_equivalentes=false;
$html_para_formulario_comparacion_especifica_tablas="";
$html_para_formulario_comparacion_especifica_tablas_reg_equivalentes="";
$contador_elementos_radio=0;

//COMPARACION NUMERO TABLAS ENTRE LAS BASES DE DATOS


if(count($matriz_datos_conexion)>0)
{
	//creacion archivo diferencias totales
	$ruta_archivo_diferencias_totales=$ruta_destino."totaldiff.csv";
	if(file_exists($ruta_archivo_diferencias_totales))
	{
		unlink($ruta_archivo_diferencias_totales);
	}//fin if

	$file_archivo_diferencias_totales = fopen($ruta_archivo_diferencias_totales, "w") or die("fallo la creacion del archivo");
	$titulo="SEP=;\nDiferencias Totales\n\"Base de Datos 1\";\"Base de Datos 2\";\"Codigo Tipo Diferencia\";\"Descripcion Tipo Diferencia\";\"Descripcion detalle diferencia\"\n";
	fwrite($file_archivo_diferencias_totales,$titulo);
}//fin if


$contador_conexiones_bd=0;
while($contador_conexiones_bd<count($matriz_datos_conexion) )
{
	$servidor=$matriz_datos_conexion[$contador_conexiones_bd]['SERVIDOR'];
	$usuario=$matriz_datos_conexion[$contador_conexiones_bd]['USUARIO'];
	$contrasena=$matriz_datos_conexion[$contador_conexiones_bd]['CONTRASENA'];
	$puerto=$matriz_datos_conexion[$contador_conexiones_bd]['PUERTO'];

	$nombre_base_de_datos=$matriz_datos_conexion[$contador_conexiones_bd]["BASE_DE_DATOS"];

	$numero_tablas=count($matriz_tablas_por_conexion[$contador_conexiones_bd]);

	$mensaje_actual="Base de Datos Actual: $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), cantidad de tablas en el esquema 'public': $numero_tablas  .";
	$html_resumen_estado.= "<br>$mensaje_actual<br>";
	ob_flush();
	flush();

	$array_tablas_de_bd_que_no_existen_en_comparada=array();

	$array_sequences_que_no_existen_en_comparada=array();

	$contador_conexiones_bd_segundo_ciclo=0;
	while($contador_conexiones_bd_segundo_ciclo<count($matriz_datos_conexion)  )
	{
		if($contador_conexiones_bd_segundo_ciclo!=$contador_conexiones_bd)
		{
			

			$cantidad_diferencias_total=0;//no incluira en su cuenta las diferencias entre los propietarios de las tablas 

			$existe_tabla=false;//se usa el que sigue mas adelante para reiniciar la verificacion de la existencia de tablas
			$nombre_base_de_datos_segundo=$matriz_datos_conexion[$contador_conexiones_bd_segundo_ciclo]["BASE_DE_DATOS"];
			$numero_tablas_segundo=count($matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo]);


			$servidor_segundo=$matriz_datos_conexion[$contador_conexiones_bd_segundo_ciclo]['SERVIDOR'];
			$usuario_segundo=$matriz_datos_conexion[$contador_conexiones_bd_segundo_ciclo]['USUARIO'];
			$contrasena_segundo=$matriz_datos_conexion[$contador_conexiones_bd_segundo_ciclo]['CONTRASENA'];
			$puerto_segundo=$matriz_datos_conexion[$contador_conexiones_bd_segundo_ciclo]['PUERTO'];

			//la creacion de archivos debe ir aca, para saber contra quien compara

			$html_para_formulario_comparacion_especifica_tablas.="<tr><td colspan='6' style='text-align:center;'><table style='margin-left: auto;margin-right: auto;width:100%;' ><tr><td><input type='button' value='Mostrar $nombre_base_de_datos VS $nombre_base_de_datos_segundo' id='mostrar_diff_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' name='mostrar_diff_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' onclick=mostrar_div('div_comp_diff_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."'); /></td></tr></table></td></tr><tr><td colspan='6'><div id='div_comp_diff_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' style='display:none;'><table>";

			$html_para_formulario_comparacion_especifica_tablas.="<tr><th>Selector</th><th><label>Nombre Tabla (Posee Un Numero De Registros Diferentes)</label></th><th>Numero Registros BD1</th><th>Numero Registros BD2</th><th>Cadena Conexion BD1</th><th>Cadena Conexion BD2</th></tr>";

			$html_para_formulario_comparacion_especifica_tablas_reg_equivalentes.="<tr><td colspan='6' style='text-align:center;'><table style='margin-left: auto;margin-right: auto;width:100%;' ><tr><td><input type='button' value='Mostrar $nombre_base_de_datos VS $nombre_base_de_datos_segundo' id='mostrar_equiv_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' name='mostrar_equiv_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' onclick=mostrar_div('div_comp_equiv_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."'); /></td></tr></table></td></tr><tr><td colspan='6'><div id='div_comp_equiv_".$contador_conexiones_bd."_vs_".$contador_conexiones_bd_segundo_ciclo."' style='display:none;'><table>";

			$html_para_formulario_comparacion_especifica_tablas_reg_equivalentes.="<tr><th>Selector</th><th><label>Nombre Tabla (Posee Un Numero De Registros Iguales)</label></th><th>Numero Registros BD1</th><th>Numero Registros BD2</th><th>Cadena Conexion BD1</th><th>Cadena Conexion BD2</th></tr>";

			//CREACION ARCHIVOS POR CONEXION

			//creacion archivo equivalencias
			$ruta_archivo_equivalencias=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_equiv.csv";
			if(file_exists($ruta_archivo_equivalencias))
			{
				unlink($ruta_archivo_equivalencias);
			}

			$file_archivo_equivalencias = fopen($ruta_archivo_equivalencias, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nEquivalencias Encontradas\n\"Base de Datos 1\";\"Base de Datos 2\";\"Codigo Tipo Diferencia\";\"Descripcion Tipo Diferencia\";\"Descripcion detalle diferencia\"\n";
			fwrite($file_archivo_equivalencias,$titulo);
			$array_rutas_archivos_equivalencias[]=$ruta_archivo_equivalencias;

			//creacion archivo diferencias para triggers
			$ruta_archivo_diferencias_triggers=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_triggers.csv";
			if(file_exists($ruta_archivo_diferencias_triggers))
			{
				unlink($ruta_archivo_diferencias_triggers);
			}

			$file_archivo_diferencias_triggers = fopen($ruta_archivo_diferencias_triggers, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Triggers\n";
			fwrite($file_archivo_diferencias_triggers,$titulo);
			$array_rutas_archivos_diferencias_triggers[]=$ruta_archivo_diferencias_triggers;

			//creacion archivo diferencias para secuencias
			$ruta_archivo_diferencias_secuencias=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_sec.csv";
			if(file_exists($ruta_archivo_diferencias_secuencias))
			{
				unlink($ruta_archivo_diferencias_secuencias);
			}

			$file_archivo_diferencias_secuencias = fopen($ruta_archivo_diferencias_secuencias, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Secuencias\n";
			fwrite($file_archivo_diferencias_secuencias,$titulo);
			$array_rutas_archivos_diferencias_secuencias[]=$ruta_archivo_diferencias_secuencias;

			//creacion archivo diferencias para funciones en bd
			$ruta_archivo_diferencias_funciones_en_bd=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_func.csv";
			if(file_exists($ruta_archivo_diferencias_funciones_en_bd))
			{
				unlink($ruta_archivo_diferencias_funciones_en_bd);
			}

			$file_archivo_diferencias_funciones_en_bd = fopen($ruta_archivo_diferencias_funciones_en_bd, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Funciones en BD\n";
			fwrite($file_archivo_diferencias_funciones_en_bd,$titulo);
			$array_rutas_archivos_diferencias_funciones_en_bd[]=$ruta_archivo_diferencias_funciones_en_bd;

			//creacion archivo diferencias para tablas
			$ruta_archivo_diferencias_tablas=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_tablas.csv";
			if(file_exists($ruta_archivo_diferencias_tablas))
			{
				unlink($ruta_archivo_diferencias_tablas);
			}

			$file_archivo_diferencias_tablas = fopen($ruta_archivo_diferencias_tablas, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Tablas\n";
			fwrite($file_archivo_diferencias_tablas,$titulo);
			$array_rutas_archivos_diferencias_tablas[]=$ruta_archivo_diferencias_tablas;

			//creacion archivo diferencias columnas
			$ruta_archivo_diferencias_col=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_cols_tablas.csv";
			if(file_exists($ruta_archivo_diferencias_col))
			{
				unlink($ruta_archivo_diferencias_col);
			}

			$file_archivo_diferencias_col = fopen($ruta_archivo_diferencias_col, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Columnas\n";
			fwrite($file_archivo_diferencias_col,$titulo);
			$array_rutas_archivos_diferencias_col[]=$ruta_archivo_diferencias_col;

			//creacion archivo diferencias restricciones
			$ruta_archivo_diferencias_rest=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_rest_tablas.csv";
			if(file_exists($ruta_archivo_diferencias_rest))
			{
				unlink($ruta_archivo_diferencias_rest);
			}

			$file_archivo_diferencias_rest = fopen($ruta_archivo_diferencias_rest, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Restricciones\n";
			fwrite($file_archivo_diferencias_rest,$titulo);
			$array_rutas_archivos_diferencias_rest[]=$ruta_archivo_diferencias_rest;

			

			//creacion archivo diferencias restricciones
			$ruta_archivo_diferencias_prop=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_diff_owners_tablas.csv";
			if(file_exists($ruta_archivo_diferencias_prop))
			{
				unlink($ruta_archivo_diferencias_prop);
			}

			$file_archivo_diferencias_prop = fopen($ruta_archivo_diferencias_prop, "w") or die("fallo la creacion del archivo");
			$titulo="SEP=;\nDiferencias Encontradas Propietarios\n";
			fwrite($file_archivo_diferencias_prop,$titulo);
			$array_rutas_archivos_diferencias_prop[]=$ruta_archivo_diferencias_prop;


			//creacion archivo SQL ALTERS ADD COLUMNS
			$ruta_archivo_alters_add_columns=$ruta_destino.$nombre_base_de_datos."_vs_".$nombre_base_de_datos_segundo."_alters_add_cols.sql";
			if(file_exists($ruta_archivo_alters_add_columns))
			{
				unlink($ruta_archivo_alters_add_columns);
			}

			$file_archivo_alters_add_columns = fopen($ruta_archivo_alters_add_columns, "w") or die("fallo la creacion del archivo");
			$titulo="--Alters Columnas de ".$nombre_base_de_datos." para ".$nombre_base_de_datos_segundo;
			fwrite($file_archivo_alters_add_columns,$titulo);
			$array_rutas_archivos_alters_col[]=$ruta_archivo_alters_add_columns;

			//FIN CREACION ARCHIVOS POR CONEXION

			$mensaje_actual="Base de Datos Actual: $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) contra $nombre_base_de_datos_segundo(conexion numero: $contador_conexiones_bd_segundo_ciclo), cantidad de tablas en el esquema 'public' de $nombre_base_de_datos : $numero_tablas  contra cantidad de tablas en el esquema 'public' de $nombre_base_de_datos_segundo $numero_tablas_segundo .";
			$html_resumen_estado.= "<br>$mensaje_actual<br>";
			$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"000\";\"Cantidad de Tablas\";\"$mensaje_actual\"";
			ob_flush();
			flush();
			fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");	
			fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");

	

			$contador_tabla_actual=0;
			while($contador_tabla_actual<count($matriz_tablas_por_conexion[$contador_conexiones_bd]))
			{


				$nombre_tabla_actual="";

				$nombre_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["tablename"];
				$propietario_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["tableowner"];		
				$espacio_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["tablespace"];
				$tiene_indices_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["hasindexes"];
				$tiene_reglas_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["hasrules"];
				$tiene_disparadores_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["hastriggers"];

				$array_columnas_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["columnas_info"];

				$array_restricciones_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["restricciones_info"];

				$registros_tabla_actual=$matriz_tablas_por_conexion[$contador_conexiones_bd][$contador_tabla_actual]["numero_registros_info"];

				//aqui verifica si la tabla existe y compara sus atributos con la tabla de nombre equivalente en la otra base de datos
				$contador_tabla_segunda=0;
				$existe_tabla=false;
				while($contador_tabla_segunda<count($matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo]) )
				{
					//tablas de la otra base de datos
					$nombre_tabla_segunda="";
					$nombre_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["tablename"];
					$propietario_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["tableowner"];		
					$espacio_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["tablespace"];
					$tiene_indices_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["hasindexes"];
					$tiene_reglas_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["hasrules"];
					$tiene_disparadores_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["hastriggers"];

					$array_columnas_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["columnas_info"];

					$array_restricciones_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["restricciones_info"];

					$registros_tabla_segunda=$matriz_tablas_por_conexion[$contador_conexiones_bd_segundo_ciclo][$contador_tabla_segunda]["numero_registros_info"];

					if($nombre_tabla_actual==$nombre_tabla_segunda)
					{
						//echo "<br>la tabla $nombre_tabla_actual si existe en esta base de datos.</br>";
						$mensaje_actual="La tabla ( $nombre_tabla_actual ) de la base de datos actual $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), si existe en la base de datos $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
						//echo "<br>$mensaje_actual<br>";						
						$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"001\";\"La tabla Existe\";\"$mensaje_actual\"";
						fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");


						//PARTE VERIFICA CANTIDAD DE REGISTROS EN TABLS CON NOMBRES EQUIVALENTES
						$numero_registro_tabla_1=intval($registros_tabla_actual);
						$numero_registro_tabla_2=intval($registros_tabla_segunda);

						
						if($numero_registro_tabla_1!=$numero_registro_tabla_2)
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, tiene numero de registros diferentes ($numero_registro_tabla_1) vs ($numero_registro_tabla_2), de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"013\";\"Numero de Registros Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
						}//fin if
						else
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, tiene numero de registros iguales ($numero_registro_tabla_1) vs ($numero_registro_tabla_2), de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"008\";\"Numero de Registros Iguales\";\"$mensaje_actual\"";
							fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");
						}//fin else
						//FIN PARTE VERIFICA CANTIDAD DE REGISTROS EN TABLS CON NOMBRES EQUIVALENTES


						$existe_tabla=true;
						//comparacion de atributos previo ala comparacion de columnas
						if($propietario_tabla_actual!=$propietario_tabla_segunda)
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, tiene propietario diferentes, tabla actual es: $propietario_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la de la base de datos comparada que es: $propietario_tabla_segunda. la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"002\";\"Propietarios Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_prop,$mensaje_actual_para_txt."\n");//debido a la nueva organizacion se re-incluyo
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");
							$cantidad_diferencias_total++; 
						}//fin if


						if($espacio_tabla_actual!=$espacio_tabla_segunda)
						{						
							$mensaje_actual="La tabla $nombre_tabla_actual, tiene espacios de tabla diferentes, actual es: $espacio_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la de la base de datos comparada que es: $espacio_tabla_segunda. la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"003\";\"Espacios de Tabla Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
						}//fin if

						if($tiene_indices_tabla_actual!=$tiene_indices_tabla_segunda)
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, (Booleano) tiene indices diferentes, tabla actual es: $tiene_indices_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la de la base de datos comparada que es: $tiene_indices_tabla_segunda. la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"004\";\"Indices Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
						}//fin if

						if($tiene_reglas_tabla_actual!=$tiene_reglas_tabla_segunda)
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, (Booleano) tiene reglas diferentes, tabla actual es: $tiene_reglas_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la de la base de datos comparada que es: $tiene_reglas_tabla_segunda. la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"005\";\"Reglas Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
						}//fin if

						if($tiene_disparadores_tabla_actual!=$tiene_disparadores_tabla_segunda)
						{
							$mensaje_actual="La tabla $nombre_tabla_actual, (Booleano) tiene disparadores diferentes, tabla actual es: $tiene_disparadores_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd), frente a la de la base de datos comparada que es: $tiene_disparadores_tabla_segunda. la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"006\";\"Disparadores Diferentes\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
						}//fin if



						//fin comparacion de atributos previo ala comparacion de columnas


						//si la tabla existe con el mismo nombre en la otra base de datos 
						//procede a comparar las columnas
						
						$ambas_tablas_poseen_todas_las_columnas_y_estas_son_iguales=true;
						$contador_columna_tabla_actual=0;
						while($contador_columna_tabla_actual<count($array_columnas_tabla_actual) )
						{
							$nombre_columna_tabla_actual=$array_columnas_tabla_actual[$contador_columna_tabla_actual]["column"];
							$tipo_dato_columna_tabla_actual=$array_columnas_tabla_actual[$contador_columna_tabla_actual]["datatype"];

							$existe_columna=false;
							$contador_columna_tabla_segunda=0;
							while($contador_columna_tabla_segunda<count($array_columnas_tabla_segunda) )
							{
								$nombre_columna_tabla_segunda=$array_columnas_tabla_segunda[$contador_columna_tabla_segunda]["column"];
								$tipo_dato_columna_tabla_segunda=$array_columnas_tabla_segunda[$contador_columna_tabla_segunda]["datatype"];
								
								if($nombre_columna_tabla_actual==$nombre_columna_tabla_segunda)
								{
									$mensaje_actual="La columna ( $nombre_columna_tabla_actual ), de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) si existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
									//echo "<br>$mensaje_actual<br>";
									$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"002\";\"Columnas equivalentes\";\"$mensaje_actual\"";
									fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");	
									

									$existe_columna=true;

									if($tipo_dato_columna_tabla_actual!=$tipo_dato_columna_tabla_segunda)
									{
										$mensaje_actual="En la tabla $nombre_columna_tabla_actual, de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd)  ,  Los tipos de datos de las columnas equivalentes comparadas son diferentes: Actual: $tipo_dato_columna_tabla_actual vs Comparada $tipo_dato_columna_tabla_segunda de la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
										//echo "<br>$mensaje_actual<br>";
										$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"007\";\"Columnas Con Diferentes Tipos de Datos\";\"$mensaje_actual\"";
											
										fwrite($file_archivo_diferencias_col,$mensaje_actual_para_txt."\n");
										fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

										$array_primer_tipo=explode("(",$tipo_dato_columna_tabla_actual);
										$solo_primer_tipo_para_cast=$array_primer_tipo[0];

										$array_segundo_tipo=explode("(",$tipo_dato_columna_tabla_segunda);
										$solo_segundo_tipo_para_cast=$array_segundo_tipo[0];

										//file_archivo_alters_add_columns
										$alter_columna_actual="";
										$alter_columna_actual.="ALTER TABLE $nombre_tabla_actual ALTER COLUMN $nombre_columna_tabla_actual TYPE $tipo_dato_columna_tabla_actual using ".$nombre_columna_tabla_actual."::".$solo_primer_tipo_para_cast." ; ";
										fwrite($file_archivo_alters_add_columns,"\n".$alter_columna_actual);

										$ambas_tablas_poseen_todas_las_columnas_y_estas_son_iguales=false;

										$cantidad_diferencias_total++;
									}
								}//fin if compara los nomrbes de las columnas de las tablas equivalentes
								$contador_columna_tabla_segunda++;
							}//fin while columnas segunda tabla equivalente

							if($existe_columna==false)
							{
								$mensaje_actual="La columna ( $nombre_columna_tabla_actual ), de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd)  , no existe en la tabla equivalente de la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
								//echo "<br>$mensaje_actual<br>$ruta_archivo_diferencias_col";
								$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"008\";\"La Columna no Existe\";\"$mensaje_actual\"";
									
								fwrite($file_archivo_diferencias_col,$mensaje_actual_para_txt."\n");
								fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");
								/*
								$valor_fwrite=fwrite($file_archivo_diferencias_col,$mensaje_actual."\n");
								echo "fwrite val $valor_fwrite .<br>";
								if($valor_fwrite==false )
								{
									echo "<br>fallo al escribir<br>";
								}
								*/

								//file_archivo_alters_add_columns
								$alter_columna_actual="";
								$alter_columna_actual.="ALTER TABLE $nombre_tabla_actual ADD COLUMN $nombre_columna_tabla_actual $tipo_dato_columna_tabla_actual ; ";
								fwrite($file_archivo_alters_add_columns,"\n".$alter_columna_actual);



								$ambas_tablas_poseen_todas_las_columnas_y_estas_son_iguales=false;

								$cantidad_diferencias_total++;
							}//fin if

							$contador_columna_tabla_actual++;
						}//fin while columnas primera tabla equivalente

						if($ambas_tablas_poseen_todas_las_columnas_y_estas_son_iguales==true)
						{
							$encontro_tablas_equivalentes=true;
							$string_conexion_actual="";
							$string_conexion_actual.=$servidor." ".$usuario." ".$contrasena." ".$nombre_base_de_datos." ".$puerto;
							$string_conexion_segunda="";
							$string_conexion_segunda.=$servidor_segundo." ".$usuario_segundo." ".$contrasena_segundo." ".$nombre_base_de_datos_segundo." ".$puerto_segundo;
							
							if($numero_registro_tabla_1!=$numero_registro_tabla_2)
							{
								$html_para_formulario_comparacion_especifica_tablas.="<tr><td><input type='radio' name='tabla_especifica' id='tabla_especifica_".$contador_elementos_radio."' value='".$nombre_tabla_actual." ".$string_conexion_actual." ".$string_conexion_segunda."'/></td><td><label>$nombre_tabla_actual</label></td><td>$numero_registro_tabla_1</td><td>$numero_registro_tabla_2</td><td>$string_conexion_actual</td><td>$string_conexion_segunda</td></tr>";
							}//fin if
							else
							{
								$html_para_formulario_comparacion_especifica_tablas_reg_equivalentes.="<tr><td><input type='radio' name='tabla_especifica' id='tabla_especifica_".$contador_elementos_radio."' value='".$nombre_tabla_actual." ".$string_conexion_actual." ".$string_conexion_segunda."'/></td><td><label>$nombre_tabla_actual</label></td><td>$numero_registro_tabla_1</td><td>$numero_registro_tabla_2</td><td>$string_conexion_actual</td><td>$string_conexion_segunda</td></tr>";
							}//fin else

							$contador_elementos_radio++;
						}//fin if
						// fin si la tabla existe con el mismo nombre en la otra base de datos 	

						//si la tabla existe con el mismo nombre en la otra base de datos 
						//procede a comparar las restriccions
						$contador_restriccion_tabla_actual=0;
						while($contador_restriccion_tabla_actual<count($array_restricciones_tabla_actual) )
						{
							$nombre_restriccion_tabla_actual=$array_restricciones_tabla_actual[$contador_restriccion_tabla_actual]["conname"];

							$existe_restriccion=false;
							$contador_restriccion_tabla_segunda=0;
							while($contador_restriccion_tabla_segunda<count($array_restricciones_tabla_segunda) )
							{
								$nombre_restriccion_tabla_segunda=$array_restricciones_tabla_segunda[$contador_restriccion_tabla_segunda]["conname"];
								
								if($nombre_restriccion_tabla_actual==$nombre_restriccion_tabla_segunda)
								{
									$mensaje_actual="La restriccion ( $nombre_restriccion_tabla_actual ), de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) si existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
									//echo "<br>$mensaje_actual<br>";
									$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"003\";\"La restriccion Existe\";\"$mensaje_actual\"";
									fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");	
									

									$existe_restriccion=true;

									foreach($array_restricciones_tabla_actual[$contador_restriccion_tabla_actual] as $key_restriccion=>$datos_restriccion)
									{
										if($key_restriccion!="conname" 
											&& $key_restriccion!="conrelid"
											&& $key_restriccion!="conindid"											
											&& $key_restriccion!="confrelid"
											)
										{
											if($datos_restriccion!=$array_restricciones_tabla_segunda[$contador_restriccion_tabla_segunda][$key_restriccion])
											{
												$mensaje_actual="El atributo $key_restriccion de la restriccion $nombre_restriccion_tabla_actual , de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) es diferente al atributo de la restriccion de la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo). valor 1: $datos_restriccion valor2: ".$array_restricciones_tabla_segunda[$contador_restriccion_tabla_segunda][$key_restriccion].".";
												//echo "<br>$mensaje_actual<br>";
												$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"009\";\"Atributo de Restriccion es Diferente\";\"$mensaje_actual\"";
													
												fwrite($file_archivo_diferencias_rest,$mensaje_actual_para_txt."\n");
												fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

												$cantidad_diferencias_total++;
											}//fin if

										}//fin if no es el nombre de la restriccion
									}//fin foreach

									
								}//fin if compara si los nombres de las restricciones de las tablas equivalentes son equivalentes
								$contador_restriccion_tabla_segunda++;
							}//fin while tabla equivalente de la segunda base de datos

							if($existe_restriccion==false)
							{
								$mensaje_actual="La restriccion ( $nombre_restriccion_tabla_actual ), de la tabla $nombre_tabla_actual de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd)  , no existe en la tabla equivalente de la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
								//echo "<br>$mensaje_actual<br>";
								$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"010\";\"La Restriccion no Existe\";\"$mensaje_actual\"";
									
								fwrite($file_archivo_diferencias_rest,$mensaje_actual_para_txt."\n");
								fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

								$cantidad_diferencias_total++;
							}//fin if

							$contador_restriccion_tabla_actual++;
						}//fin while restricciones tabla equivalente de la primera base de datos
						// fin si la tabla existe con el mismo nombre en la otra base de datos				

					}//fin if
					$contador_tabla_segunda++;
				}//fin while ciclo tablas segunda base de datos
				if($existe_tabla==false)
				{
					$mensaje_actual="VET=La tabla ( $nombre_tabla_actual ) de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) NO existe en la base de datos  comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
					$html_resumen_estado.= "<br>$mensaje_actual<br>";
					$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"001\";\"La tabla no existe\";\"$mensaje_actual\"";
					
					//echo $ruta_archivo_diferencias_tablas." ".$ruta_archivo_diferencias_totales." ".$mensaje_actual_para_txt."<br>";

					fwrite($file_archivo_diferencias_tablas,$mensaje_actual_para_txt."\n");
					fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

					if($generar_sql_estructuras==1 || $generar_sql_estructuras==2)
					{

						$ruta_archivo_dump_sql_tabla=$ruta_destino."dump_".$nombre_base_de_datos."_".$nombre_tabla_actual.".backup";
						$array_rutas_dumps_tablas[]=$ruta_archivo_dump_sql_tabla;

						$array_tablas_de_bd_que_no_existen_en_comparada[]=$nombre_tabla_actual;

						if($generar_sql_estructuras==3 || $generar_sql_estructuras==4)
						{
							$solo_schema="";
							if($generar_sql_estructuras==1 || $generar_sql_estructuras==3)
							{
								$solo_schema=" --schema-only ";
							}//fin if

							$comando_pg_dump="";
							if(strpos(strtolower($user_os), "ubuntu")!==false || strpos(strtolower($user_os), "linux")!==false)
							{								
								$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								//$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump -h $servidor -p $puerto -U $usuario -d $nombre_base_de_datos -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								shell_exec("nohup  $comando_pg_dump ");
							}//fin if es linux ubuntu
							else if(strpos(strtolower($user_os), "windows")!==false)
							{
								$comando_pg_dump=$ruta_global_funciones_postgres_bin_windows."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								//$comando_pg_dump=$ruta_global_funciones_postgres_bin_windows."pg_dump.exe -h $servidor -p $puerto -U $usuario -d $nombre_base_de_datos -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								pclose(popen("cmd /c start $comando_pg_dump ", "r"));
							}//else if  es window
							else
							{
								$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								//$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump -h $servidor -p $puerto -U $usuario -d $nombre_base_de_datos -t $nombre_tabla_actual -f $ruta_archivo_dump_sql_tabla -F c $solo_schema";
								shell_exec("nohup  $comando_pg_dump ");
							}//version desconocida de sistema operativo
							//echo $comando_pg_dump."<br>";
						}//fin if $generar_sql_estructuras==3 o $generar_sql_estructuras==4
					}//fin if se generan dumps

					$cantidad_diferencias_total++;
				}//fin if

						
				$contador_tabla_actual++;
			}//fin while ciclo tablas primera base de datos

			$contador_secuencia_actual=0;
			if(isset($matriz_secuencias_por_conexion[$contador_conexiones_bd]['nombres_secuencias']) )
			{
				while($contador_secuencia_actual<count($matriz_secuencias_por_conexion[$contador_conexiones_bd]['nombres_secuencias']) )
				{
					$nombre_secuencia_actual=$matriz_secuencias_por_conexion[$contador_conexiones_bd]['nombres_secuencias'][$contador_secuencia_actual]['relname'];
					
					$contador_secuencia_actual_segunda=0;
					$existe_secuencia=false;
					while($contador_secuencia_actual_segunda<count($matriz_secuencias_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_secuencias']) )
					{
						$nombre_secuencia_actual_segunda=$matriz_secuencias_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_secuencias'][$contador_secuencia_actual_segunda]['relname'];

						if($nombre_secuencia_actual==$nombre_secuencia_actual_segunda)
						{
							$mensaje_actual="La secuencia ( $nombre_secuencia_actual ), de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) si existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"004\";\"La Secuencia Si Existe\";\"$mensaje_actual\"";
							fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");	
							
							$existe_secuencia=true;
						}//fin if

						$contador_secuencia_actual_segunda++;
					}//fin while
					if($existe_secuencia==false)
					{
						$mensaje_actual="La secuencia ( $nombre_secuencia_actual ), de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) NO existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
						$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"011\";\"La Secuencia no Existe\";\"$mensaje_actual\"";

						$array_sequences_que_no_existen_en_comparada[]=$nombre_secuencia_actual;
								
							fwrite($file_archivo_diferencias_secuencias,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
					}//fin if
					$contador_secuencia_actual++;
				}//fin while secuencias
			}//fin if


			$contador_funciones_en_bd_actual=0;
			if(isset($matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones']) )
			{
				while($contador_funciones_en_bd_actual<count($matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones']) )
				{
					//echo print_r($matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones'],true)."<br>";
					$nombre_funciones_en_bd_actual=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones'][$contador_funciones_en_bd_actual]['routine_name'];
					$data_type_funcion=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones'][$contador_funciones_en_bd_actual]['data_type'];
					$ordinal_position_funcion=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd]['nombres_funciones'][$contador_funciones_en_bd_actual]['ordinal_position'];
					
					$contador_funciones_en_bd_actual_segunda=0;
					$existe_funciones_en_bd=false;
					while($contador_funciones_en_bd_actual_segunda<count($matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_funciones']) )
					{
						$nombre_funciones_en_bd_actual_segunda=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_funciones'][$contador_funciones_en_bd_actual_segunda]['routine_name'];
						$data_type_funcion_segunda=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_funciones'][$contador_funciones_en_bd_actual_segunda]['data_type'];
						$ordinal_position_funcion_segunda=$matriz_funciones_en_bd_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_funciones'][$contador_funciones_en_bd_actual_segunda]['ordinal_position'];


						if($nombre_funciones_en_bd_actual==$nombre_funciones_en_bd_actual_segunda)
						{
							$mensaje_actual="La funcion en bd ( $nombre_funciones_en_bd_actual ) , de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) si existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"005\";\"La Funcion Si Existe\";\"$mensaje_actual\"";
							fwrite($file_archivo_equivalencias,$mensaje_actual_para_txt."\n");	
							
							$existe_funciones_en_bd=true;
						}//fin if

						$contador_funciones_en_bd_actual_segunda++;
					}//fin while
					if($existe_funciones_en_bd==false)
					{
						$mensaje_actual="La funcion en bd ( $nombre_funciones_en_bd_actual ) , de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) NO existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
						$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"012\";\"La Funcion no Existe\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_funciones_en_bd,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
					}//fin if
					$contador_funciones_en_bd_actual++;
				}//fin while funciones_en_bds
			}//fin if

			$contador_triggers_actual=0;
			if(isset($matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers']) )
			{
				while($contador_triggers_actual<count($matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers']) )
				{
					$nombre_triggers_actual=$matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers'][$contador_triggers_actual]['tgname'];
					$trigger_type_funcion=$matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers'][$contador_triggers_actual]['trigger_type'];
					$trigger_event_funcion=$matriz_triggers_por_conexion[$contador_conexiones_bd]['nombres_triggers'][$contador_triggers_actual]['trigger_event'];
					
					$contador_triggers_actual_segunda=0;
					$existe_triggers=false;
					while(isset($matriz_triggers_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_triggers'])
						&& $contador_triggers_actual_segunda<count($matriz_triggers_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_triggers']) )
					{
						$nombre_triggers_actual_segunda=$matriz_triggers_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_triggers'][$contador_triggers_actual_segunda]['tgname'];
						$trigger_type_funcion_segunda=$matriz_triggers_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_triggers'][$contador_triggers_actual_segunda]['trigger_type'];
						$trigger_event_funcion_segunda=$matriz_triggers_por_conexion[$contador_conexiones_bd_segundo_ciclo]['nombres_triggers'][$contador_triggers_actual_segunda]['trigger_event'];


						if($nombre_triggers_actual==$nombre_triggers_actual_segunda)
						{
							$mensaje_actual="El trigger en bd ( $nombre_triggers_actual ) , de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) si existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
							$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"006\";\"El Trigger Si Existe\";\"$mensaje_actual\"";
							fwrite($file_archivo_equivalencias,$mensaje_actual."\n");	
							
							$existe_triggers=true;
						}//fin if

						$contador_triggers_actual_segunda++;
					}//fin while
					if($existe_triggers==false)
					{
						$mensaje_actual="El trigger en bd ( $nombre_triggers_actual ), de la base de datos $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) NO existe en la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo).";
							//echo "<br>$mensaje_actual<br>";
						$mensaje_actual_para_txt="\"$nombre_base_de_datos\";\"$nombre_base_de_datos_segundo\";=\"012\";\"El Trigger no Existe\";\"$mensaje_actual\"";
								
							fwrite($file_archivo_diferencias_triggers,$mensaje_actual_para_txt."\n");
							fwrite($file_archivo_diferencias_totales,$mensaje_actual_para_txt."\n");

							$cantidad_diferencias_total++;
					}//fin if
					$contador_triggers_actual++;
				}//fin while triggers
			}//fin if

			fclose($file_archivo_equivalencias);
			fclose($file_archivo_diferencias_tablas);
			fclose($file_archivo_diferencias_col);
			fclose($file_archivo_diferencias_rest);
			fclose($file_archivo_diferencias_prop);
			fclose($file_archivo_diferencias_secuencias);
			fclose($file_archivo_diferencias_funciones_en_bd);
			fclose($file_archivo_diferencias_triggers);

			fclose($file_archivo_alters_add_columns);

			$html_resumen_estado.= "Numero Total Diferencias Encontradas ($cantidad_diferencias_total) entre $nombre_base_de_datos (conexion numero: $contador_conexiones_bd) y la base de datos comparada $nombre_base_de_datos_segundo (conexion numero: $contador_conexiones_bd_segundo_ciclo) <br>";

			$html_para_formulario_comparacion_especifica_tablas.="</table></div></td></tr>";
			$html_para_formulario_comparacion_especifica_tablas_reg_equivalentes.="</table></div></td></tr>";
		}//fin if para evitar que se compare contra si mismo
		$contador_conexiones_bd_segundo_ciclo++;
	}//fin while segundas conexiones 

	if($generar_sql_estructuras==1 
		|| $generar_sql_estructuras==2
		|| $generar_sql_estructuras==3
		|| $generar_sql_estructuras==4
	)//fin condicion
	{

		

		$solo_schema="";
		if($generar_sql_estructuras==1)
		{
			$solo_schema=" --schema-only ";
		}//fin if

		$formato_dump="c";
		if(count($array_tablas_de_bd_que_no_existen_en_comparada)>0)
		{
			$ruta_archivo_dump_sql_tabla=$ruta_destino."dump_".$nombre_base_de_datos."_tablas_faltantes_en_comparada.backup";
			array_unshift($array_rutas_dumps_tablas, $ruta_archivo_dump_sql_tabla);
			$string_tablas_faltantes="";
			foreach ($array_tablas_de_bd_que_no_existen_en_comparada as $key => $nombre_tabla_faltante) 
			{
				$string_tablas_faltantes.=" -t ".$nombre_tabla_faltante." ";
			}//fin foreach

			$comando_pg_dump="";
			if(strpos(strtolower($user_os), "ubuntu")!==false || strpos(strtolower($user_os), "linux")!==false)
			{								
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_tablas_faltantes -f $ruta_archivo_dump_sql_tabla -F $formato_dump $solo_schema";
				shell_exec("nohup  $comando_pg_dump ");
			}//fin if es linux ubuntu
			else if(strpos(strtolower($user_os), "windows")!==false)
			{
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_windows."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_tablas_faltantes -f $ruta_archivo_dump_sql_tabla -F $formato_dump $solo_schema";
				pclose(popen("cmd /c start $comando_pg_dump ", "r"));
			}//else if  es window
			else
			{
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_tablas_faltantes -f $ruta_archivo_dump_sql_tabla -F $formato_dump $solo_schema";
				shell_exec("nohup  $comando_pg_dump ");
			}//version desconocida de sistema operativo
		}//fin if
		//echo $comando_pg_dump."<br>";

		

		$formato_dump="p";
		if(count($array_sequences_que_no_existen_en_comparada)>0)
		{
			$ruta_archivo_dump_sql_sequences=$ruta_destino."dump_".$nombre_base_de_datos."_secuencias_faltantes_en_comparada.sql";
			array_unshift($array_rutas_dumps_sequences, $ruta_archivo_dump_sql_sequences);
			$string_sequences_faltantes="";
			foreach ($array_sequences_que_no_existen_en_comparada as $key => $sequence_faltante) 
			{
				$string_sequences_faltantes.=" -t ".$sequence_faltante." ";
			}//fin foreach

			$comando_pg_dump="";
			if(strpos(strtolower($user_os), "ubuntu")!==false || strpos(strtolower($user_os), "linux")!==false)
			{								
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_sequences_faltantes -f $ruta_archivo_dump_sql_sequences -F $formato_dump $solo_schema";
				shell_exec("nohup  $comando_pg_dump ");
			}//fin if es linux ubuntu
			else if(strpos(strtolower($user_os), "windows")!==false)
			{
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_windows."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_sequences_faltantes -f $ruta_archivo_dump_sql_sequences -F $formato_dump $solo_schema";
				pclose(popen("cmd /c start $comando_pg_dump ", "r"));
			}//else if  es window
			else
			{
				$comando_pg_dump=$ruta_global_funciones_postgres_bin_linux."pg_dump --dbname=postgresql://".$usuario.":".$contrasena."@".$servidor.":".$puerto."/".$nombre_base_de_datos." $string_sequences_faltantes -f $ruta_archivo_dump_sql_sequences -F $formato_dump $solo_schema";
				shell_exec("nohup  $comando_pg_dump ");
			}//version desconocida de sistema operativo
		}//fin if
		//echo $comando_pg_dump."<br>";
	}//fin if se generan dumps

	$contador_conexiones_bd++;

	
}//fin while primeras conexiones
//FIN COMPARACION NUMERO TABLAS ENTRE LAS BASES DE DATOS
fclose($file_archivo_diferencias_totales);

$html_inicio_tabla="";
$html_inicio_tabla.="<table style='margin-left: auto;margin-right: auto;'>";

$html_final_tabla="";
$html_final_tabla.="</table>";

//PARTE ENLANCES DESCARGA RESULTADOS
$html_enlaces_descarga_archivos_generados="";
$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_archivo_diferencias_totales' target='_blank'>Archivo Diferencias Totales</a></td></tr>";

foreach ($array_rutas_archivos_alters_col as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Alter Tablas: Add Columnas $nombre_file</a></td></tr>";
}//fin foreach

if($generar_sql_estructuras==1 
	|| $generar_sql_estructuras==2
	|| $generar_sql_estructuras==3
	|| $generar_sql_estructuras==4
)//fin if
{
	foreach ($array_rutas_dumps_tablas as $key => $ruta_actual) 
	{
		$array_ruta_actual=explode("/", $ruta_actual);
		$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
		$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Dump SQL Tabla: $nombre_file</a></td></tr>";
	}//fin foreach
}//fin if

//array_rutas_dumps_sequences
if($generar_sql_estructuras==1 
	|| $generar_sql_estructuras==2
	|| $generar_sql_estructuras==3
	|| $generar_sql_estructuras==4
)//fin if
{
	foreach ($array_rutas_dumps_sequences as $key => $ruta_actual) 
	{
		$array_ruta_actual=explode("/", $ruta_actual);
		$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
		$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Dump SQL Secuencias: $nombre_file</a></td></tr>";
	}//fin foreach
}//fin if

foreach ($array_rutas_archivos_diferencias_tablas as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Tablas $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_diferencias_col as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Tablas: Columnas $nombre_file</a></td></tr>";
}//fin foreach


foreach ($array_rutas_archivos_diferencias_rest as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Tablas: Restricciones $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_diferencias_prop as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Tablas: Propietarios $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_diferencias_secuencias as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Secuencias $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_diferencias_triggers as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Triggers $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_diferencias_funciones_en_bd as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Diferencias Funciones $nombre_file</a></td></tr>";
}//fin foreach

foreach ($array_rutas_archivos_equivalencias as $key => $ruta_actual) 
{
	$array_ruta_actual=explode("/", $ruta_actual);
	$nombre_file=$array_ruta_actual[count($array_ruta_actual)-1];
	$html_enlaces_descarga_archivos_generados.="<tr><td><a href='$ruta_actual' target='_blank'>Archivo Equivalencias $nombre_file</a></td></tr>";
}//fin foreach
//FIN PARTE ENLANCES DESCARGA RESULTADOS



if(count($matriz_datos_conexion)>0)
{
	echo "<script>document.getElementById('div_archivos_generados').innerHTML=\"$html_inicio_tabla $html_enlaces_descarga_archivos_generados $html_final_tabla\";</script>";
}//fin if

if($html_para_formulario_comparacion_especifica_tablas!="" && $encontro_tablas_equivalentes==true)
{
	echo "<script>var nom_tabla_reg_diferentes='div_tabla_registros_diferentes';var nom_tabla_reg_iguales='div_tabla_registros_iguales';</script>";

	$html_para_formulario_comparacion_especifica_tablas_parte_final="";
	$html_para_formulario_comparacion_especifica_tablas_parte_final.="<tr><td colspan='6'><input type='button' value='Comparar Tablas Equivalentes' style='margin-left: auto;margin-right: auto;' onclick='ejecutar_comparacion_especifica_tabla();'/><input type='hidden' id='ruta_destino_carpeta_comparaciones_hidden' name='ruta_destino_carpeta_comparaciones_hidden' value='".str_replace("/", "123separador123", $ruta_destino)."' /><label><input type='checkbox' id='reemplazar_nulos' name='reemplazar_nulos' value='' />Reemplazar Valores Nulos por <input type='text' id='reemplazo_del_nulo' name='reemplazo_del_nulo' value='SD'/></label></td></tr>";
	$html_para_formulario_comparacion_especifica_tablas_parte_final.="<tr><td colspan='6'><div id='div_respuesta_consulta_especifica_tabla' style='margin-left: auto;margin-right: auto;'></div></td></tr>";

	$botones_mostrar_diff="<tr><td colspan='6'><input type='button' value='Mostrar tablas equivalentes con registros diferentes en numero' onclick='mostrar_div(nom_tabla_reg_diferentes);'/></td></tr>";

	$botones_mostrar_equiv="<tr><td colspan='6'><input type='button' value='Mostrar tablas equivalentes con registros equivalentes en numero' onclick='mostrar_div(nom_tabla_reg_iguales);'/></td></tr>";
	
	echo "<script>document.getElementById('div_comparador_especifico_tablas').innerHTML=\"$html_inicio_tabla $botones_mostrar_diff <tr><td colspan='6'><div id='div_tabla_registros_diferentes' style='display:none;width:100%;'><table style='width:100%;'> $html_para_formulario_comparacion_especifica_tablas </table></div></td></tr> $botones_mostrar_equiv <tr><td colspan='6'><div id='div_tabla_registros_iguales' style='display:none;width:100%;'><table style='width:100%;'> $html_para_formulario_comparacion_especifica_tablas_reg_equivalentes </table></div></td></tr> $html_para_formulario_comparacion_especifica_tablas_parte_final $html_final_tabla\";</script>";
	
}//fin if

if(count($matriz_datos_conexion)>0)
{
	echo "<script>document.getElementById('div_pre_estado').style.display='inline';</script>";
}//fin if
echo "<script>document.getElementById('div_estado').innerHTML=\"$html_resumen_estado\";</script>";

//echo print_r($matriz_tablas_por_conexion,true);
?>