<?php
set_time_limit(936000);
ini_set('max_execution_time', 936000);
ini_set('memory_limit', '900M');
error_reporting(E_ERROR);


class clase_conexion_bd
{

    //put your code here
    var $servidor;
    var $usuario;
    var $pass;
    var $bd;
    var $conexion;
    var $puerto;

	function __construct($SERVIDOR,$USUARIO,$CONTRASENA,$BASE_DE_DATOS,$PUERTO) 
	{	
		$this->servidor = $SERVIDOR;
		$this->usuario = $USUARIO;
		$this->pass = $CONTRASENA;
		$this->bd = $BASE_DE_DATOS;
		$this->puerto = $PUERTO;
	}
	
	
	public function consultar($sql,&$error) 
	{
		$error = "";
		//$this->crearConexion();		
		$query= pg_fetch_all(pg_query($this->conexion, $sql));
		if($query)
		{}
		else
		{
			//$this->escribir_en_log_errores($sql,"consultar_no_warning_get_error_no_crea_cierra p1");
			$error = "".pg_last_error($this->conexion); 
		}
		//$this->cerrar_conexion();
		//parte para que sirva count()==0 si no hay resultados
		if (!$query) 
		{
			//$this->escribir_en_log_errores($sql,"consultar_no_warning_get_error_no_crea_cierra p2");
		} 
		else 
		{
			return $query;
		}
    }//fin function consultar

    function estado_conexion()
    {
    	if(isset($this->conexion) && pg_connection_status($this->conexion)===PGSQL_CONNECTION_BAD )
    	{
    		return "fallida";
    	}
    	else if(isset($this->conexion) && pg_connection_status($this->conexion)===PGSQL_CONNECTION_OK )
    	{
    		return "conectado";
    	}
    }
	
	
	
	public function insertar($sql,&$error) 
	{
		$error = "";
		$bandera = false;
		
		
		if(@pg_exec($this->conexion, $sql))
		{
			$bandera = true;
		}
		else
		{
		 $error = "".pg_last_error($this->conexion); 
		 
		}
		
		return $bandera;
    }//fin insertar
	
	

	public function crear_conexion($mostrar_mensajes=false)
	{	
	    if(connection_aborted()==false && $mostrar_mensajes==true)
	    {
			echo "<br>Preparando la conexion<br>";
	    }//fin if

	    $cadena_de_coneccion="host=".$this->servidor." port=".$this->puerto." dbname=".$this->bd." user=".$this->usuario." password=".$this->pass;
	   
	   	$this->conexion = pg_connect($cadena_de_coneccion,PGSQL_CONNECT_FORCE_NEW) or die("Fallo la conexion a la Base de datos! cadena de conexion usada: $cadena_de_coneccion" . pg_last_error()); 	    
	    

	    if(connection_aborted()==false && $mostrar_mensajes==true)
	    {
			echo "<br>Se creo la conexion<br>";
	    }//fin if
	}//fin crearConexion

	public function cerrar_conexion($mostrar_mensajes=false)
	{	
		if(connection_aborted()==false && $mostrar_mensajes==true)
	    {
			echo "<br>Se va a cerrar la conexion<br>";
	    }//fin if

		pg_close($this->conexion);

		if(connection_aborted()==false && $mostrar_mensajes==true)
	    {
			echo "<br>Se cerro la conexion<br>";
	    }//fin if
	}
	
	public function reconectar()
	{
		try
		{
			cerrar_conexion();
		}catch(Exception $e)
		{}

		try
		{
			crear_conexion();
		}catch(Exception $e)
		{}
	}

}//fin clase

?>
