<?php
set_time_limit(936000);
ini_set('max_execution_time', 936000);
ini_set('memory_limit', '900M');
ini_set('log_errors', 1);
//ini_set('error_log', 'error_log_comparaciones_especifico_bd.log');
require_once('clase_conexion_bd.php');

function poner_doble_comilla($value)
{
	return "\"".$value."\"";
}

function poner_comilla_simple($value)
{
	return "'".$value."'";
}

if(isset($_REQUEST['cadena']))
{
	$cadena=trim($_REQUEST['cadena']);
	$cadena=trim($cadena,"\"");
	$array_cadena=explode(" ", $cadena);

	$reemplazar_nulos=trim($_REQUEST['reemplazar_nulos']);
	$valor_reemplazo_nulos=str_replace("\"", "",  trim($_REQUEST['valor_reemplazo_nulos']) );

	$tabla_equivalente=$array_cadena[0];

	$servidor_1=$array_cadena[1];
	$usuario_1=$array_cadena[2];
	$contrasena_1=$array_cadena[3];
	$base_de_datos_1=$array_cadena[4];
	$puerto_1=$array_cadena[5];

	$servidor_2=$array_cadena[6];
	$usuario_2=$array_cadena[7];
	$contrasena_2=$array_cadena[8];
	$base_de_datos_2=$array_cadena[9];
	$puerto_2=$array_cadena[10];

	$ruta_destino_carpeta_comparaciones_hidden="";
	if(isset($_REQUEST['ruta_carpeta_comparaciones']) )
	{
		$ruta_carpeta_comparaciones=trim($_REQUEST['ruta_carpeta_comparaciones']);
		$ruta_carpeta_comparaciones=str_replace("123separador123", "/", $ruta_carpeta_comparaciones);
	}//fin if

	$ruta_completa_log_comparaciones=$ruta_carpeta_comparaciones."log_comp_entre_tablas.log";
	ini_set('error_log', $ruta_completa_log_comparaciones);

	$numero_registros_1=0;
	$numero_registros_2=0;

	$conexion_1= new clase_conexion_bd($servidor_1,$usuario_1,$contrasena_1,$base_de_datos_1,$puerto_1);
	$conexion_2= new clase_conexion_bd($servidor_2,$usuario_2,$contrasena_2,$base_de_datos_2,$puerto_2);
	$conexion_1->crear_conexion();
	$conexion_2->crear_conexion();

	$query_numero_registros="SELECT COUNT(*) as numero_registros from $tabla_equivalente ; ";
	
	$error_query="";
	$resultado_nr=array();
	$estado_conn="conectado";
	do
	{
		if($estado_conn=="fallida" )
		{
			error_log("conn 1 reconectando para query ".$query_numero_registros);
			$conexion_1->reconectar();
		}

		$resultado_nr=$conexion_1->consultar($query_numero_registros,$error_query);
		if( $error_query!="")
		{
			$estado_conn=$conexion_1->estado_conexion();
		}
	}while($estado_conn=="fallida" );
	if(count($resultado_nr)>0 && is_array($resultado_nr) )
	{
		$numero_registros_1=$resultado_nr[0]['numero_registros'];
	}//fin if

	$error_query="";
	$resultado_nr=array();
	$estado_conn="conectado";
	do
	{
		if($estado_conn=="fallida" )
		{
			error_log("conn 2 reconectando para query ".$query_numero_registros);
			$conexion_2->reconectar();
		}

		$resultado_nr=$conexion_2->consultar($query_numero_registros,$error_query);
		if( $error_query!="")
		{
			$estado_conn=$conexion_2->estado_conexion();
		}
	}while($estado_conn=="fallida" );
	if(count($resultado_nr)>0 && is_array($resultado_nr) )
	{
		$numero_registros_2=$resultado_nr[0]['numero_registros'];
	}//fin if

	


	

	$numero_registros_bloque=5000;
	$offset_1=0;
	$offset_2=0;

	$ruta_archivo_inserts_conexion_1="insertsCon1".$tabla_equivalente.".csv";
	$ruta_archivo_inserts_conexion_2="insertsCon2".$tabla_equivalente.".csv";

	$ruta_completa_1=$ruta_carpeta_comparaciones.$ruta_archivo_inserts_conexion_1;
	$handle_1=fopen($ruta_completa_1, 'w');
	

	fwrite($handle_1,"SEP=|\nTabla ".$tabla_equivalente." Conexion ".$servidor_1." ".$usuario_1." ".$contrasena_1." ".$base_de_datos_1." ".$puerto_1." Numero Registros: ".$numero_registros_1);


	$cont_registros_extraidos_1=0;
	while($offset_1<$numero_registros_1)
	{
		$query_registros_tabla="SELECT * from $tabla_equivalente LIMIT $numero_registros_bloque OFFSET $offset_1 ; ";
		
		$resultado_1=array();
		$error_query="";
		$estado_conn="conectado";
		do
		{
			if($estado_conn=="fallida" )
			{
				error_log("conn1 reconectando para query ".$query_registros_tabla);
				$conexion_1->reconectar();
			}
			$resultado_1=$conexion_1->consultar($query_registros_tabla,$error_query);
			if( $error_query!="")
			{
				$estado_conn=$conexion_1->estado_conexion();
			}
		}while($estado_conn=="fallida" );

		$seccion_values="";
		$contador_inserts=0;
		$contador_acumulado_foreach_actual=0;
		$bloque_inserts=1000;
		if(count($resultado_1)>0 && is_array($resultado_1) )
		{
			error_log("conn1 se consulto un bloque de $numero_registros_bloque registros con el offset $offset_1");
			$array_nombre_columnas=array_map('poner_doble_comilla',  array_keys($resultado_1[0]) );

			foreach ($resultado_1 as $key => $value) 
			{			
				$array_valores=array_values($value);

				$array_columnas_definitivas=array();
				$array_valores_definitivos=array();

				$cont_columnas=0;
				while($cont_columnas<count($array_nombre_columnas) )
				{
					if($array_valores[$cont_columnas]!="")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]=poner_comilla_simple($array_valores[$cont_columnas]);
					}//fin if
					else if($reemplazar_nulos=="SI")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]=poner_comilla_simple($valor_reemplazo_nulos);
					}//fin else if
					else if($reemplazar_nulos=="NO")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]="NULL";
					}//fin if
					$cont_columnas++;
				}//fin while

				$string_nombres_columnas_def=implode(",", $array_columnas_definitivas);
				$string_valores_columnas_def=implode(",", $array_valores_definitivos);

				if($reemplazar_nulos=="NO")
				{
					$valor_reemplazo_nulos="nada";
				}

				if($seccion_values!=""){$seccion_values.=",";}
				$seccion_values.=" ( $string_valores_columnas_def ) ";
				$contador_inserts++;

				$linea_query_insert_generada="";
				if($contador_inserts==$bloque_inserts || $contador_acumulado_foreach_actual==(count($resultado_1)-1) )
				{					
					$contador_inserts=0;
					$linea_query_insert_generada="INSERT INTO $tabla_equivalente ($string_nombres_columnas_def) VALUES $seccion_values ;";
					fwrite($handle_1,"\n".$linea_query_insert_generada);
					$seccion_values="";
				}//fin if ya tiene $bloque_inserts filas en la parte de seccion values

				
				$contador_acumulado_foreach_actual++;

				$cont_registros_extraidos_1++;
			}//fin foreach

		}//fin if

		$offset_1=$offset_1+$numero_registros_bloque;
	}//fin while
	fclose($handle_1);

	$ruta_completa_2=$ruta_carpeta_comparaciones.$ruta_archivo_inserts_conexion_2;
	$handle_2=fopen($ruta_completa_2, 'w');
	fwrite($handle_2,"SEP=|\nTabla ".$tabla_equivalente." Conexion ".$servidor_2." ".$usuario_2." ".$contrasena_2." ".$base_de_datos_2." ".$puerto_2." Numero Registros: ".$numero_registros_2);

	$cont_registros_extraidos_2=0;
	while($offset_2<$numero_registros_2)
	{
		$query_registros_tabla="SELECT * from $tabla_equivalente LIMIT $numero_registros_bloque OFFSET $offset_2 ; ";
		
		$resultado_2=array();
		$error_query="";
		$estado_conn="conectado";
		do
		{
			if($estado_conn=="fallida" )
			{
				error_log("conn2 reconectando para query ".$query_registros_tabla);
				$conexion_2->reconectar();
			}
			$resultado_2=$conexion_2->consultar($query_registros_tabla,$error_query);
			if( $error_query!="")
			{
				$estado_conn=$conexion_2->estado_conexion();
			}
		}while($estado_conn=="fallida" );

		$seccion_values="";
		$contador_inserts=0;
		$contador_acumulado_foreach_actual=0;
		$bloque_inserts=1000;
		if(count($resultado_2)>0 && is_array($resultado_2) )
		{
			error_log("conn2 se consulto un bloque de $numero_registros_bloque registros con el offset $offset_2");
			$array_nombre_columnas=array_map('poner_doble_comilla',  array_keys($resultado_2[0]) );

			foreach ($resultado_2 as $key => $value) 
			{
				$array_valores=array_values($value);

				$array_columnas_definitivas=array();
				$array_valores_definitivos=array();

				$cont_columnas=0;
				while($cont_columnas<count($array_nombre_columnas) )
				{
					if($array_valores[$cont_columnas]!="")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]=poner_comilla_simple($array_valores[$cont_columnas]);
					}//fin if
					else if($reemplazar_nulos=="SI")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]=poner_comilla_simple($valor_reemplazo_nulos);
					}//fin else if
					else if($reemplazar_nulos=="NO")
					{
						$array_columnas_definitivas[]=$array_nombre_columnas[$cont_columnas];
						$array_valores_definitivos[]="NULL";
					}//fin if
					$cont_columnas++;
				}//fin while

				$string_nombres_columnas_def=implode(",", $array_columnas_definitivas);
				$string_valores_columnas_def=implode(",", $array_valores_definitivos);

				if($reemplazar_nulos=="NO")
				{
					$valor_reemplazo_nulos="nada";
				}


				if($seccion_values!=""){$seccion_values.=",";}
				$seccion_values.=" ( $string_valores_columnas_def ) ";
				$contador_inserts++;

				$linea_query_insert_generada="";
				if($contador_inserts==$bloque_inserts || $contador_acumulado_foreach_actual==(count($resultado_2)-1) )
				{
					$contador_inserts=0;
					$linea_query_insert_generada="INSERT INTO $tabla_equivalente ($string_nombres_columnas_def) VALUES $seccion_values ;";
					fwrite($handle_2,"\n".$linea_query_insert_generada);
					$seccion_values="";
				}//fin if ya tiene $bloque_inserts filas en la parte de seccion values

				$contador_acumulado_foreach_actual++;

				$cont_registros_extraidos_2++;
			}//fin foreach
		}//fin if

		$offset_2=$offset_2+$numero_registros_bloque;
	}//fin while
	fclose($handle_2);


	$string_si_hubo_reemplazo_nulos="<br>$reemplazar_nulos se reemplazo valores considerados nulos, por $valor_reemplazo_nulos<br>";


	$conexion_1->cerrar_conexion();
	$conexion_2->cerrar_conexion();
	echo "La tabla  $tabla_equivalente tiene ($numero_registros_1) vs ($numero_registros_2) de la conexion $servidor_1 BD: $base_de_datos_1 contra $servidor_2 BD: $base_de_datos_2. ( $ruta_carpeta_comparaciones )
		<br>
		Archivo 1: <a href='$ruta_completa_1' target='_blank'>Inserts Conexion 1 (Extraidos $cont_registros_extraidos_1 de $numero_registros_1)</a>
		<input type='button' value='Realizar inserts De Conexion 1 ($servidor_1 $base_de_datos_1) a Conexion 2 ($servidor_2 $base_de_datos_2)' onclick=\"ejecutar_inserts('$ruta_completa_1','$servidor_2','$usuario_2','$contrasena_2', '$base_de_datos_2', '$puerto_2');\"/>
		<br>
		Archivo 2: <a href='$ruta_completa_2' target='_blank'>Inserts Conexion 2 (Extraidos $cont_registros_extraidos_2 de $numero_registros_2)
		</a>
		<input type='button' value='Realizar inserts De Conexion 2 ($servidor_2 $base_de_datos_2) a Conexion 1 ($servidor_1 $base_de_datos_1)' onclick=\"ejecutar_inserts('$ruta_completa_2','$servidor_1','$usuario_1','$contrasena_1', '$base_de_datos_1', '$puerto_1');\"/>
		<br>
		<div id='resultado_inserts_especifica_tabla'/></div>
		$string_si_hubo_reemplazo_nulos
		";
}//fin if isset
?>