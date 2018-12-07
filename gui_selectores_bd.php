<!DOCTYPE html>
<html>
<head>
	<title>Comparador Estructura Bases de Datos</title>
</head>
<body >
<form method='post' id='formulario' name='formulario'>
<h1 style='text-align: center;'>Comparador De Estructura Para Bases de Datos Postgresql</h1>
	<table style='text-align: center;margin-left:auto;margin-right:auto;'>

		<tr>
			<td>
				<div id='div_selector_conexiones_guardadas'  >
					<select id='conexionesguardadas' name='conexionesguardadas' onmouseenter='llenar_selector_consultas_previas();' >
						<option value='none'>Seleccione Una Consulta Previamente Realizada</option>
					</select>
				</div>
			</td>
		</tr>

		<tr>
			<td>
				<label>Diligencie si desea, el nombre con el cual se guardara la informacion diligenciada de las conexiones a comparar.
				<br> De lo contrario el aplicativo generara un nombre propio de acuerdo a las bases de datos comparadas.
				</label>
				<br>
				<input type='text' id='nombre_a_guardar_consulta_conexiones' name='nombre_a_guardar_consulta_conexiones' placeholder='Nombre Consulta Conexiones'/>
				
			</td>
		</tr>

		<tr>
			<td>
				<label>Limites de la comparacion:
					<select name='limites_comparacion' id='limites_comparacion' >
						<option value='0' <?php if($_REQUEST['limites_comparacion']=='0'){ echo "selected"; }?> >Estructura</option>
						<option value='1' <?php if($_REQUEST['limites_comparacion']=='1'){ echo "selected"; }?> >Datos y Estructura</option>
					</select>
				</label>
			</td>
		</tr>

		<tr>
			<td>
				<label>Generar SQL tablas faltantes:
					<select name='generar_sql_estructuras' id='generar_sql_estructuras' >
						<option value='0' <?php if($_REQUEST['generar_sql_estructuras']=='0'){ echo "selected"; }?> >NO</option>
						<option value='1' <?php if($_REQUEST['generar_sql_estructuras']=='1'){ echo "selected"; }?>>Solo Estructura</option>
						<option value='2' <?php if($_REQUEST['generar_sql_estructuras']=='2'){ echo "selected"; }?>>Datos y Estructura</option>
						<option value='3' <?php if($_REQUEST['generar_sql_estructuras']=='3'){ echo "selected"; }?>>Solo Estructura y Dump Por Separado Para Cada Tabla</option>
						<option value='4' <?php if($_REQUEST['generar_sql_estructuras']=='4'){ echo "selected"; }?>>Datos y Estructura y Dump Por Separado Para Cada Tabla</option>
					</select>
				</label>
			</td>
		</tr>

		<tr>
			<td>
				<input type='button' name='adicionar' id='adicionar' value='Adicionar Mas Conexiones' onclick='adicionarConexiones();' />
				<input type='button' name='quitar' id='quitar' value='Quitar Ultima Conexion' onclick='quitarConexiones();' />
				<input type='text' name='cantidad_especifica_ini' id='cantidad_especifica_ini' placeholder='Cantidad'>
				<input type='button' name='arbitrario' id='arbitrario' value='Cantidad Especifica de Conexiones' onclick="llamarCantidadEspecifica('cantidad_especifica_ini');" />
				<input type='button' name='limpiar' id='limpiar' value='Limpiar' onclick='limpiarCamposConexionesExistentes();' />
				<input type='button' name='reset' id='reset' value='Reset' onclick='llamarReset();' />
				<input type='button' name='enviar' id='enviar' value='Enviar' onclick='validar_y_enviar();' />
			</td>
		</tr>	

		<tr>
			<td>
				<div id='div_datos_conexion_bds'>
				<table style='margin-left: auto;margin-right: auto;'>	

					<tr><th>SERVIDORES</th><th>USUARIO</th><th>CONTRASENA</th><th>BASE DE DATOS</th><th>PUERTO</th></tr>

					<tr>
						<td>
							<input type='text' name='servidor_bd1' id='servidor_bd1' placeholder='SERVIDOR 1' />
						</td>
						<td>
							<input type='text' name='usuario_bd1' id='usuario_bd1' placeholder='USUARIO 1' />
						</td>
						<td>
							<input type='text' name='contrasena_bd1' id='contrasena_bd1' placeholder='CONTRASENA 1' />
						</td>
						<td>
							<input type='text' name='base_de_datos_bd1' id='base_de_datos_bd1' placeholder='BASE DE DATOS 1' />
						</td>
						<td>
							<input type='text' name='puerto_bd1' id='puerto_bd1' placeholder='PUERTO 1' />
						</td>
					</tr>

					<tr>
						<td>
							<input type='text' name='servidor_bd2' id='servidor_bd2' placeholder='SERVIDOR 2' />
						</td>
						<td>
							<input type='text' name='usuario_bd2' id='usuario_bd2' placeholder='USUARIO 2' />
						</td>
						<td>
							<input type='text' name='contrasena_bd2' id='contrasena_bd2' placeholder='CONTRASENA 2' />
						</td>
						<td>
							<input type='text' name='base_de_datos_bd2' id='base_de_datos_bd2' placeholder='BASE DE DATOS 2' />
						</td>
						<td>
							<input type='text' name='puerto_bd2' id='puerto_bd2' placeholder='PUERTO 2' />
						</td>
					</tr>

				</table>
				</div>
			</td>
		</tr>

		<tr>
			<td>
				<input type='button' name='adicionar' id='adicionar' value='Adicionar Mas Conexiones' onclick='adicionarConexiones();' />
				<input type='button' name='quitar' id='quitar' value='Quitar Ultima Conexion' onclick='quitarConexiones();' />
				<input type='text' name='cantidad_especifica_fin' id='cantidad_especifica_fin' placeholder='Cantidad' />
				<input type='button' name='arbitrario' id='arbitrario' value='Cantidad Especifica de Conexiones' onclick="llamarCantidadEspecifica('cantidad_especifica_fin');" />
				<input type='button' name='limpiar' id='limpiar' value='Limpiar' onclick='limpiarCamposConexionesExistentes();' />
				<input type='button' name='reset' id='reset' value='Reset' onclick='llamarReset();' />
				<input type='button' name='enviar' id='enviar' value='Enviar' onclick='validar_y_enviar();' />
			</td>
		</tr>

		<tr>
			<td>
				<input type='hidden' name='submit_signal' id='submit_signal'/>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_oculto' style='display: none;'></div>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_visor1' class='visor_log' ></div>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_respuesta' style='text-align: center; vertical-align: middle;'></div>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_comparador_especifico_tablas' style='text-align: center; vertical-align: middle;'></div>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_archivos_generados' style='text-align: center; vertical-align: middle;'></div>
			</td>
		</tr>

		<tr>
			<td>
				<div id='div_pre_estado' style='text-align: center; vertical-align: middle;display:none;'>
					<table style='margin-left: auto;margin-right: auto;'>
						<tr>
							<td>
								<input type='button' id='mostrar_estado' name='mostrar_estado' value='Mostrar Resumen Estado' onclick="mostrar_div('div_estado');" />
							</td>
						</tr>
						<tr>
							<td>
								<div id='div_estado' style='text-align: center; vertical-align: middle;display:none;'></div>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>

	</table>
</form>
<script>


function adicionarConexiones()
{
	var array_servidores=[];
	var array_usuarios=[];
	var array_contrasenas=[];
	var array_bases_de_datos=[];
	var array_puertos=[];

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('servidor_bd'+cont))
	{
		array_servidores[cont]=document.getElementById('servidor_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('usuario_bd'+cont))
	{
		array_usuarios[cont]=document.getElementById('usuario_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('contrasena_bd'+cont))
	{
		array_contrasenas[cont]=document.getElementById('contrasena_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('base_de_datos_bd'+cont))
	{
		array_bases_de_datos[cont]=document.getElementById('base_de_datos_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('puerto_bd'+cont))
	{
		array_puertos[cont]=document.getElementById('puerto_bd'+cont).value;
		cont++;
	}//fin while

	var html_inputs="";

	html_inputs+="<table style='margin-left: auto;margin-right: auto;'>";
	html_inputs+="<tr><th>SERVIDORES</th><th>USUARIO</th><th>CONTRASENA</th><th>BASE DE DATOS</th><th>PUERTO</th></tr>";
	var cont_rewrite=1;
	while(cont_rewrite<=array_bases_de_datos.length )//se cambio hasta menor para cuando sea indefinido adicione los nuevos inputs
	{
		html_inputs+="<tr>";


		if(typeof array_servidores[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR "+cont_rewrite+"' value='"+array_servidores[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR U "+cont_rewrite+"' />";
		    html_inputs+="</td>";	
		}//fin else

		if(typeof array_usuarios[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO "+cont_rewrite+"' value='"+array_usuarios[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else


		if(typeof array_contrasenas[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA "+cont_rewrite+"' value='"+array_contrasenas[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		if(typeof array_bases_de_datos[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS "+cont_rewrite+"' value='"+array_bases_de_datos[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			//doesnt exist
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		if(typeof array_puertos[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO "+cont_rewrite+"' value='"+array_puertos[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		html_inputs+="</tr>";
		cont_rewrite++;
	}//fin while
	html_inputs+="</table>";


	document.getElementById('div_datos_conexion_bds').innerHTML=html_inputs;


}//fin function

function adicionarConexionesArbitrario(cantidad)
{
	var array_servidores=[];
	var array_usuarios=[];
	var array_contrasenas=[];
	var array_bases_de_datos=[];
	var array_puertos=[];

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('servidor_bd'+cont))
	{
		array_servidores[cont]=document.getElementById('servidor_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('usuario_bd'+cont))
	{
		array_usuarios[cont]=document.getElementById('usuario_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('contrasena_bd'+cont))
	{
		array_contrasenas[cont]=document.getElementById('contrasena_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('base_de_datos_bd'+cont))
	{
		array_bases_de_datos[cont]=document.getElementById('base_de_datos_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('puerto_bd'+cont))
	{
		array_puertos[cont]=document.getElementById('puerto_bd'+cont).value;
		cont++;
	}//fin while

	var cantidad_entero=parseInt(cantidad);

	if(cantidad_entero === parseInt(cantidad, 10) )
	{

		var html_inputs="";

		html_inputs+="<table style='margin-left: auto;margin-right: auto;'>";
		html_inputs+="<tr><th>SERVIDORES</th><th>USUARIO</th><th>CONTRASENA</th><th>BASE DE DATOS</th><th>PUERTO</th></tr>";
		var cont_rewrite=1;
		while(cont_rewrite<=cantidad_entero )//se cambio hasta menor para cuando sea indefinido adicione los nuevos inputs
		{
			html_inputs+="<tr>";


			if(typeof array_servidores[cont_rewrite] !== 'undefined') 
			{
			    // does exist
			    html_inputs+="<td>";
			    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR "+cont_rewrite+"' value='"+array_servidores[cont_rewrite]+"' />";
			    html_inputs+="</td>";
			}//fin if no es indefinido
			else
			{
				html_inputs+="<td>";
			    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR U "+cont_rewrite+"' />";
			    html_inputs+="</td>";	
			}//fin else

			if(typeof array_usuarios[cont_rewrite] !== 'undefined') 
			{
			    // does exist
			    html_inputs+="<td>";
			    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO "+cont_rewrite+"' value='"+array_usuarios[cont_rewrite]+"' />";
			    html_inputs+="</td>";
			}//fin if no es indefinido
			else
			{
				html_inputs+="<td>";
			    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO U "+cont_rewrite+"' />";
			    html_inputs+="</td>";
			}//fin else


			if(typeof array_contrasenas[cont_rewrite] !== 'undefined') 
			{
			    // does exist
			    html_inputs+="<td>";
			    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA "+cont_rewrite+"' value='"+array_contrasenas[cont_rewrite]+"' />";
			    html_inputs+="</td>";
			}//fin if no es indefinido
			else
			{
				html_inputs+="<td>";
			    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA U "+cont_rewrite+"' />";
			    html_inputs+="</td>";
			}//fin else

			if(typeof array_bases_de_datos[cont_rewrite] !== 'undefined') 
			{
			    // does exist
			    html_inputs+="<td>";
			    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS "+cont_rewrite+"' value='"+array_bases_de_datos[cont_rewrite]+"' />";
			    html_inputs+="</td>";
			}//fin if no es indefinido
			else
			{
				//doesnt exist
				html_inputs+="<td>";
			    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS U "+cont_rewrite+"' />";
			    html_inputs+="</td>";
			}//fin else

			if(typeof array_puertos[cont_rewrite] !== 'undefined') 
			{
			    // does exist
			    html_inputs+="<td>";
			    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO "+cont_rewrite+"' value='"+array_puertos[cont_rewrite]+"' />";
			    html_inputs+="</td>";
			}//fin if no es indefinido
			else
			{
				html_inputs+="<td>";
			    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO U "+cont_rewrite+"' />";
			    html_inputs+="</td>";
			}//fin else

			html_inputs+="</tr>";
			cont_rewrite++;
		}//fin while
		html_inputs+="</table>";

		if(cantidad_entero>=2)
		{
			document.getElementById('div_datos_conexion_bds').innerHTML=html_inputs;
		}
	}//fin if
	else 
	{
		alert("No es un Numero Entero");
	}//fin else if


}//fin function

function quitarConexiones()
{
	var array_servidores=[];
	var array_usuarios=[];
	var array_contrasenas=[];
	var array_bases_de_datos=[];
	var array_puertos=[];

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('servidor_bd'+cont))
	{
		array_servidores[cont]=document.getElementById('servidor_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('usuario_bd'+cont))
	{
		array_usuarios[cont]=document.getElementById('usuario_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('contrasena_bd'+cont))
	{
		array_contrasenas[cont]=document.getElementById('contrasena_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('base_de_datos_bd'+cont))
	{
		array_bases_de_datos[cont]=document.getElementById('base_de_datos_bd'+cont).value;
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('puerto_bd'+cont))
	{
		array_puertos[cont]=document.getElementById('puerto_bd'+cont).value;
		cont++;
	}//fin while

	var html_inputs="";

	html_inputs+="<table style='margin-left: auto;margin-right: auto;'>";
	html_inputs+="<tr><th>SERVIDORES</th><th>USUARIO</th><th>CONTRASENA</th><th>BASE DE DATOS</th><th>PUERTO</th></tr>";
	var cont_rewrite=1;
	while(cont_rewrite<(array_bases_de_datos.length-1) )//se cambio hasta menor para cuando sea indefinido adicione los nuevos inputs
	{
		html_inputs+="<tr>";


		if(typeof array_servidores[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR "+cont_rewrite+"' value='"+array_servidores[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='servidor_bd"+cont_rewrite+"' id='servidor_bd"+cont_rewrite+"' placeholder='SERVIDOR U "+cont_rewrite+"' />";
		    html_inputs+="</td>";	
		}//fin else

		if(typeof array_usuarios[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO "+cont_rewrite+"' value='"+array_usuarios[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='usuario_bd"+cont_rewrite+"' id='usuario_bd"+cont_rewrite+"' placeholder='USUARIO U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else


		if(typeof array_contrasenas[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA "+cont_rewrite+"' value='"+array_contrasenas[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='contrasena_bd"+cont_rewrite+"' id='contrasena_bd"+cont_rewrite+"' placeholder='CONTRASENA U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		if(typeof array_bases_de_datos[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS "+cont_rewrite+"' value='"+array_bases_de_datos[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			//doesnt exist
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='base_de_datos_bd"+cont_rewrite+"' id='base_de_datos_bd"+cont_rewrite+"' placeholder='BASE DE DATOS U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		if(typeof array_puertos[cont_rewrite] !== 'undefined') 
		{
		    // does exist
		    html_inputs+="<td>";
		    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO "+cont_rewrite+"' value='"+array_puertos[cont_rewrite]+"' />";
		    html_inputs+="</td>";
		}//fin if no es indefinido
		else
		{
			html_inputs+="<td>";
		    html_inputs+="<input type='text' name='puerto_bd"+cont_rewrite+"' id='puerto_bd"+cont_rewrite+"' placeholder='PUERTO U "+cont_rewrite+"' />";
		    html_inputs+="</td>";
		}//fin else

		html_inputs+="</tr>";
		cont_rewrite++;
	}//fin while
	html_inputs+="</table>";

	if((array_bases_de_datos.length-1)>2)
	{
		document.getElementById('div_datos_conexion_bds').innerHTML=html_inputs;
	}//fin if


}//fin function

function llamarCantidadEspecifica(idDomCantidad)
{
	var cantidad=document.getElementById(idDomCantidad).value.trim();

	if(!isNaN(cantidad))
	{
		adicionarConexionesArbitrario(cantidad);
	}//fin if
	else
	{
		alert('Inserte un Numero');
	}//fin else
}//fin function

function llamarReset()
{
	adicionarConexionesArbitrario(2);
	limpiarCamposConexionesExistentes();
}//fin function

function limpiarCamposConexionesExistentes()
{
	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('servidor_bd'+cont))
	{
		document.getElementById('servidor_bd'+cont).value="";
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('usuario_bd'+cont))
	{
		document.getElementById('usuario_bd'+cont).value="";
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('contrasena_bd'+cont))
	{
		document.getElementById('contrasena_bd'+cont).value="";
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('base_de_datos_bd'+cont))
	{
		document.getElementById('base_de_datos_bd'+cont).value="";
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('puerto_bd'+cont))
	{
		document.getElementById('puerto_bd'+cont).value="";
		cont++;
	}//fin while
}//fin function

function validar_y_enviar()
{
	var hay_elemento_vacio=false;
	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('servidor_bd'+cont))
	{
		if(document.getElementById('servidor_bd'+cont).value.trim()=="")
		{
			hay_elemento_vacio=true;
		}
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('usuario_bd'+cont))
	{
		if(document.getElementById('usuario_bd'+cont).value.trim()=="")
		{
			hay_elemento_vacio=true;
		}
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('contrasena_bd'+cont))
	{
		if(document.getElementById('contrasena_bd'+cont).value.trim()=="")
		{
			hay_elemento_vacio=true;
		}
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('base_de_datos_bd'+cont))
	{
		if(document.getElementById('base_de_datos_bd'+cont).value.trim()=="")
		{
			hay_elemento_vacio=true;
		}
		cont++;
	}//fin while

	var cont=1;//inicia desde uno, debido a que no hay elemento con descriptivo desde cero
	while(document.getElementById('puerto_bd'+cont))
	{
		if(document.getElementById('puerto_bd'+cont).value.trim()=="")
		{
			hay_elemento_vacio=true;
		}
		cont++;
	}//fin while

	if(hay_elemento_vacio==false)
	{
		document.getElementById('submit_signal').value='verificado';
		document.getElementById('formulario').submit();
	}
	else
	{
		alert('Diligencie Todos Los Campos');
	}
}//fin function validar y enviar

//especificar none si el resultado de la peticion ajax no sera contenida en un div
//es asincrona, no pone warning
function ConsultaAJAX_Async(parametros,filePHP,divContent)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(divContent!="none")
	{
	    xmlhttp.onreadystatechange=function()
	    {
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
		    document.getElementById(divContent).innerHTML=xmlhttp.responseText;
		}
	    }
	    
	    xmlhttp.open("GET",filePHP+"?"+parametros+"&campodiv="+divContent,true);
	    xmlhttp.send();
		
	}
	else
	{
	    xmlhttp.onreadystatechange=function()
	    {
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
		    alert(xmlhttp.responseText);
		    return xmlhttp.responseText;
		}
	    }
	    
	    xmlhttp.open("GET",filePHP+"?"+parametros,true);
	    xmlhttp.send();
		
	}

}//fin funcion consulta ajax

function llenar_selector_consultas_previas()
{
	//alert("entro");
	ConsultaAJAX_Async("accion=llenar","ajax_listar_archivos_consultas_previas.php","div_selector_conexiones_guardadas");
}//fin function

async function seleccionar_archivo_conexion_previa()
{
	var opcion_seleccionada=document.getElementById('conexionesguardadas').value.trim();

	var array_nombre_archivo=opcion_seleccionada.split(".");

	if(array_nombre_archivo[0]=="none")
	{
		array_nombre_archivo[0]="";
	}//fin if

	document.getElementById('nombre_a_guardar_consulta_conexiones').value=array_nombre_archivo[0];

	if(opcion_seleccionada!="none" && opcion_seleccionada!="")
	{
		//alert(opcion_seleccionada);
		LeerArchivoPlanoDeServidor("consultas_almacenadas/"+opcion_seleccionada,"div_oculto");
		await sleep(500);
		var textoAlmacenado=document.getElementById("div_oculto").innerHTML;
		//alert(textoAlmacenado);
		var arraytextoAlmacenado= textoAlmacenado.split("\n");
		var matrizTextoAlmacenado= new Array();
		var cont=0;
		var contMatriz=0;
		while(cont<arraytextoAlmacenado.length)
		{
			var arrayTemp=arraytextoAlmacenado[cont].split("|");
			if(arrayTemp.length==5)
			{
				matrizTextoAlmacenado[contMatriz]=arrayTemp;
				contMatriz++;				
			}//fin if
			cont++;
		}//fin while

		var cantidadConexionesEnMatriz=matrizTextoAlmacenado.length;
		document.getElementById('cantidad_especifica_ini').value=cantidadConexionesEnMatriz;
		document.getElementById('cantidad_especifica_fin').value=cantidadConexionesEnMatriz;
		llamarCantidadEspecifica('cantidad_especifica_fin');
		var contFiltros=1;
		var cont2=0;
		while(cont2<cantidadConexionesEnMatriz)
		{
			if(matrizTextoAlmacenado[cont2].length==5)
			{
				document.getElementById('servidor_bd'+contFiltros).value=matrizTextoAlmacenado[cont2][0];
				document.getElementById('usuario_bd'+contFiltros).value=matrizTextoAlmacenado[cont2][1];
				document.getElementById('contrasena_bd'+contFiltros).value=matrizTextoAlmacenado[cont2][2];
				document.getElementById('base_de_datos_bd'+contFiltros).value=matrizTextoAlmacenado[cont2][3];
				document.getElementById('puerto_bd'+contFiltros).value=matrizTextoAlmacenado[cont2][4];
				contFiltros++;
			}//fin if
			cont2++;
		}//fin while
	}//fin if
}//fin function

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function LeerArchivoPlanoDeServidor(ruta,elemento_div)
{
	var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() 
    {
        if (xhr.readyState == 4 && xhr.status == 200) 
        {
            document.getElementById(elemento_div).innerHTML = xhr.responseText;
        }//fin if
    }//fin function
    xhr.open('GET', ruta);
    xhr.send();
}//fin function

function ejecutar_comparacion_especifica_tabla()
{
	var opciones_tabla_especifica = document.getElementsByName('tabla_especifica');
	var opcion_seleccionada_tabla_especifica="";
	for(var i = 0; i < opciones_tabla_especifica.length; i++)
	{
	    if(opciones_tabla_especifica[i].checked)
	    {
	        opcion_seleccionada_tabla_especifica = opciones_tabla_especifica[i].value;
	    }//fin if
	}//fin for

	//alert(opcion_seleccionada_tabla_especifica);

	var ruta_carpeta_comparaciones=document.getElementById('ruta_destino_carpeta_comparaciones_hidden').value;
	
	var reemplazar_nulos="NO";
	var valor_reemplazo_nulos="";

	if(document.getElementById('reemplazar_nulos') )
	{
		if(document.getElementById('reemplazar_nulos').checked==true )
		{
			reemplazar_nulos="SI";
			valor_reemplazo_nulos=document.getElementById('reemplazo_del_nulo').value;
		}//fin if
	}//fin if existe

	if(opcion_seleccionada_tabla_especifica!="")
	{
		document.getElementById('div_respuesta_consulta_especifica_tabla').innerHTML="Espere Mientras se Realiza la Operacion...<br><div id='div_visor2' class='visor_log' ></div>";
		ConsultaAJAX_Async("ruta_carpeta_comparaciones="+ruta_carpeta_comparaciones+"&cadena=\""+opcion_seleccionada_tabla_especifica+"\""+"&reemplazar_nulos="+reemplazar_nulos+"&valor_reemplazo_nulos=\""+valor_reemplazo_nulos+"\"","ajax_consulta_especifica_tabla.php","div_respuesta_consulta_especifica_tabla");
	}//fin if
	else
	{
		document.getElementById('div_respuesta_consulta_especifica_tabla').innerHTML="Seleccione Una Tabla A Comparar...<br><div id='div_visor2' class='visor_log' ></div>";
	}
 
}//fin function

function ejecutar_inserts(ruta_archivo_inserts,servidor_contrario,usuario_contrario,contrasena_contrario, base_de_datos_contrario, puerto_contrario)
{
	document.getElementById('resultado_inserts_especifica_tabla').innerHTML="Espere Mientras se Realiza la Operacion de Insercion...<br><div id='div_visor3' class='visor_log' ></div>";
	if(ruta_archivo_inserts!="" && servidor_contrario!="" && usuario_contrario!="" && contrasena_contrario!="" && base_de_datos_contrario!="" && puerto_contrario!="")
	{
		ConsultaAJAX_Async("ruta_archivo_inserts="+ruta_archivo_inserts+"&servidor_contrario="+servidor_contrario+"&usuario_contrario="+usuario_contrario+"&contrasena_contrario="+contrasena_contrario+"&base_de_datos_contrario="+base_de_datos_contrario+"&puerto_contrario="+puerto_contrario,"ajax_inserts_especifica_tabla.php","resultado_inserts_especifica_tabla");
	}//fin if
}//fin function


function mostrar_div(id_div)
{
	if(document.getElementById(id_div))
	{
		if(document.getElementById(id_div).style.display=='none')
		{
			document.getElementById(id_div).style.display='inline-block';
		}//fin if
		else
		{
			document.getElementById(id_div).style.display='none';
		}//fin else
	}//fin if existe
}//fin function

function mostrar_ultima_linea_log(ruta_log,div_id)
{
	if(document.getElementById(div_id))
	{
		if(ruta_log!="" && div_id!="")
		{
			//alert('ruta_log='+ruta_log+' div_id='+div_id);
			ConsultaAJAX_Async("ruta_log="+ruta_log+"&accion=ultima","ajax_leer_archivo.php",div_id);
		}//fin if
	}//fin if div existe
}//fin function

</script>
<style type="text/css">
	.visor_log
	{
		background: white;
		color:black;
		text-align: center;
		vertical-align: middle;
		border: white;
	}

	.visor_log:hover
	{
		background: black;
		color:white;
		text-align: center;
		vertical-align: middle;
		border: gray;
	}
</style>
</body>
</html>
