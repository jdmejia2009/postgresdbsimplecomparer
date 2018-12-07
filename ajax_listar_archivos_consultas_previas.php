<?php
$request=$_REQUEST;
if(isset($request['accion'])
	&& trim($request['accion'])=="llenar"
	)
{
	$array_lista_archivos=array();
	$array_lista_archivos=scandir("consultas_almacenadas");

	$html_selector="";
	$html_selector.="<select id='conexionesguardadas' name='conexionesguardadas' onchange='seleccionar_archivo_conexion_previa();' >
						<option value='none'>Seleccione Una Consulta Previamente Realizada</option>";
	foreach ($array_lista_archivos as $key => $archivo_actual)
	{
		if($archivo_actual!="." && $archivo_actual!="..")
		{
			$html_selector.="<option value='".$archivo_actual."'>$archivo_actual</option>";
		}//fin if
	}//fin if
	$html_selector.="</select>";
	
	echo $html_selector;
	
}//fin if

?>