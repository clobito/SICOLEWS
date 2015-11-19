<?php
	/*Propósito: Carga de de imágen que representa el colegio a través del WebService.
	Parámetro: Código del colegio
	Uso: http://servidor/colegio/colegio3.php?cod_col=yy
	Fecha creado: 27/10/2015	
	Autor: DANE
	DIRECCIÓN DE INVESTIGACIONES EN GEOESTADÍSTICA
	GRUPO DE TRABAJO DANE MODERNO
	*/
	header("content-type: application/json; charset=utf-8");
	header("access-control-allow-origin: *");	

	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '')
	{
		$vals			=	$_REQUEST;
		$vals['entidad']=	'SICOLE_TABLA_SEDE';	
		$metodo			=	'getConsultarColegioCodImg';
		//Información de la conexión a la BD
		$user 			=	'dane_gfueind';
		$clave			=	'sicole';
		$host 			=	'sige-scan:1521';
		$servicio		=	'sige';
		//Enlace al módulo de seguridad
		/*include_once('filtroseguridad.php');
		$estadoRequest = validarRequestPeticionServicio($_GET);
		if ($estadoRequest == "false")
		{
			//*********************************************************
			//*********************************************************
			//Redireccionamos a la página de error
			//Hubo intento de XSS o SQL Injection
			$sentenciaError = obtenerErrorRequest($_GET);
			$mensajeError = "Entrada Sospechosa XSS/SQL Injection Se encontró la palabra [" . $sentenciaError . "]. Emplee la tecla Retroceder del Navegador para regresar y resolver este problema.";
			$_SESSION["mensajeError"] = $mensajeError;
			header('Location: /laboratorio/serviciosjson/catastro/paginaerror.php');
			//*********************************************************
			//*********************************************************
		}
		else
		{
			//Enlace a la conexión
			include_once('databaseClass.php');
			//Intanciación de objetos	
			$databObj 	=	new databaseClass($user,$host,$clave,$servicio);	
			//Conexión a la BD
			$conexion	=	$databObj -> connect();			
			//Objeto al modelo
			$colegioObj = 	new colegioModel();
			echo json_encode($colegioObj -> $metodo($vals,$conexion));	
		}*/
		//Enlace a la conexión
		include_once('databaseClass.php');
		//Intanciación de objetos	
		$databObj 	=	new databaseClass($user,$host,$clave,$servicio);	
		//Conexión a la BD
		$conexion	=	$databObj -> connect();			
		//Objeto al modelo
		$colegioObj = 	new colegioModel();
		//Validación para carga de la imágen. Si no existe, se genera el mensaje "no tiene asociada imágen!". De lo contrario, lo visualiza.
		if ($colegioObj -> $metodo($vals,$conexion) != null)
		{
			//Encabezado de la imagen
			header("Content-type: image/jpeg");	
			print $colegioObj -> $metodo($vals,$conexion);	
		}
		else
		{
			print "El colegio con ID"." ".$vals['cod_col']." "."no tiene asociada imágen!";
		}
	}
	else
	{
		echo "Sin parametros";
		echo "\n"."Modo de uso: http://<servidor>/colegio3.php?cod_col=h";
		echo "\n"."Dónde: <servidor>: IP del servidor donde se encuentra el webservice";
		echo "\n"."h corresponde al código del colegio";
	}	
	class colegioModel
	{
		function getConsultarColegioCodImg($params,$conexion)
		{
			/*Fecha creado: 27/10/2015
			Propósito: Obtener imágen de colégios con código conocido
			Autor: DANE
			Parametros: 1.$params => array. 2.$conexion => String
			Fecha actualizado: 28/10/2015
			Cambio realizado: Validar cuando se carguen imágenes de colegios a la BD. Cuando no se encuentra imágen, se devuelve null. De lo contrario, se devuelve la imágen*/
			$campos 	=	'"FOTO"';
			$arregloData=	array();
			//Creación de la consulta
			$qry 		= 	"SELECT $campos"." "."FROM"." ".$params['entidad'];
			//Armado de la condición de la consulta
			$condic	=	"WHERE"." "."COD_COL"."="."'".$params['cod_col']."'";
			$qry	.=	" ".$condic;
			//Ejecución del query
			$sql 	=	oci_parse($conexion, $qry);
			if (!oci_execute($sql))
			{
				$e 	=	oci_error($sql);
				return $e;
			}
			//Armado del array
			while (($resultArr = oci_fetch_array($sql, OCI_ASSOC+OCI_RETURN_NULLS)) != false)
			{	
				//Imagén relacionada
				if ($resultArr['FOTO'] != null)
				{
					$imgCol 	=	$resultArr['FOTO']->load();
				}
				else
				{
					$imgCol 	=	null;
				}	
				
			}			
			return $imgCol;
		}
	}
