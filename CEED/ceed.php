<?php
	/*Propósito: Carga de información proyecto CEED
	Uso: http://servidor/CEED/ceed.php?numcenso=xx&fasemes=yy&dpto=zz&&munic=ww
	Fecha creado: 25/12/2015	
	Autor: DANE
	DIRECCIÓN DE INVESTIGACIONES EN GEOESTADÍSTICA
	GRUPO DE TRABAJO DANE MODERNO
	*/
	header("content-type: application/json; charset=utf-8");
	header("access-control-allow-origin: *");
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '')
	{
		$vals				=	$_REQUEST;
		$vals['entidad']	=	'CEED_BASE_COMPLETA';
		$vals['entidad2']	=	'CEED_CARGAS';
		//$vals['entidad3']	=	'SICOLE_CALENDARIO';
		$metodo				=	'getConsultarCeed';
		//Información de la conexión a la BD
		$user 				=	'DANE_BOD_ESPA';
		$clave				=	'bod_espa';
		$host 				=	'sige-scan:1521';
		$servicio			=	'sige';
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
		echo json_encode($colegioObj -> $metodo($vals,$conexion));		
	}
	else
	{	
		echo "Sin parametros";
		echo "\nModo de uso: http://<servidor>/ceed.php?numcenso=xx&fasemes=yy&dpto=zz&&munic=ww";
		echo "\nDonde: <servidor>: IP del servidor donde se encuentra el webservice";
		echo "\nx.x: Número censo";
		echo "\ny.y: Número fase mes";
		echo "\nz: Código Departamento";
		echo "\nw: Cópigo Municipio";
	}

	class colegioModel
	{
		function getConsultarCeed($params,$conexion)
		{
			/*Fecha creado: 15/12/2015
			Propósito: Formar la consulta de tuplas desde la BD.
			Autor: DANE
			Parametros: 1.$params => array. 2.$conexion => String						
			Observaciones: URL => http://localhost/colegio/ceed.php?numcenso=77&fasemes=1&dpto=15&&munic=001*/
			$arrayData = array();
			$denominador=	$this -> getConsultarDenom($params,$conexion);
			$numerador 	=	$this -> getConsultarNumer($params,$conexion);
			array_push($arrayData,$numerador);
			array_push($arrayData,$denominador);
			return $arrayData;
		}
		function getConsultarDenom($params,$conexion)
		{	
			/*Fecha creado: 15/12/2015
			Propósito: Generar el denominador del indicador CEED.
			Autor: DANE
			Parametros: 1.$params => array. 2.$conexion => String
			*/
			//Zona de declaraciones
			//$campos 	=	'"'.$params['entidad'].'"."COD_COL","'.$params['entidad'].'"."NOM_COL","'.$params['entidad'].'"."SECTOR","'.$params['entidad'].'"."DIR_COL","'.$params['entidad'].'"."TEL_COL","'.$params['entidad'].'"."EMAIL","'.$params['entidad'].'"."WEB_INST","'.$params['entidad'].'"."PREESCOLAR","'.$params['entidad'].'"."PRIMARIA","'.$params['entidad'].'"."SECUNDARIA","'.$params['entidad'].'"."MEDIA","'.$params['entidad'].'"."LATITUD","'.$params['entidad'].'"."LONGITUD","'.$params['entidad'].'"."CARACTER","'.$params['entidad'].'"."TOR_MAT"';
			$campos 	=	$params['entidad'].'.numero_censo,'.$params['entidad'].'.codigo_departamento,'.$params['entidad'].'.departamento,'.$params['entidad'].'.codigo_municipio';
			$campos		.= 	',';
			$campos		.=	$params['entidad'].'.fase_mes,'.$params['entidad'].'.FECHA_DILIGENCIAMIENTO';
			$agreg		=	',COUNT('.''.'UNIQUE'.'('.$params['entidad'].'.LLAVE_IDS)) AS "TOTAL"';
			//Niveles de educación desde entidad2			
			//$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."MEDIA") AS "MEDIA"';			
			$arregloData=	array();
			//Creación de la consulta
			//$qry 		= 	"SELECT $campos"." "."FROM"." ".$params['entidad2'];
			$qry 		= 	"SELECT $campos"."$agreg"." "."FROM"." ".$params['entidad'];
			//Consulta 2
			$campos2	=	"LLAVE_IDS";
			$qry2		=	"SELECT $campos2"." "."FROM"." ".$params['entidad'];
			//Condición Consulta 2
			$condic2	=	"WHERE"." "."NUMERO_CENSO=".$params['numcenso'];
			$condic2 	.=	" "."AND"." "."("."TIPO_OBRA='1'"." "."OR"." "."("."TIPO_OBRA=2"." "."AND"." "."ESTADO_ACTUAL<>'3'"."))";
			//Actualización de la consulta 2
			$qry2		.=	" ".$condic2;
			//Actualización de la consulta, cargar ETIQUETA desde la entidad dada en el array $params => entidad3
			$condic 	=	"WHERE"." "."NUMERO_CENSO=".$params['numcenso']." "."AND"." ".$params['entidad']."."."fase_mes"."=".$params['fasemes'];
			$condic 	.=	" "."AND"." ".$params['entidad']."."."codigo_departamento"."=".$params['dpto']." "."AND"." ".$params['entidad']."."."codigo_municipio"."="."'".$params['munic']."'"; 
			$condic 	.=	" "."AND"." ".$params['entidad']."."."LLAVE_IDS"." "."IN"." "."(".$qry2.")";
			$agrupar	=	"GROUP BY"." ".$campos;
			//Ordenamiento
			$order 		=	"ORDER BY"." ".$campos;			
			//Armado de la consulta
			$qry	.=	" ".$condic." ".$agrupar." ".$order;			
			//Ejecución del query
			$sql 	=	oci_parse($conexion, $qry);
			if (!oci_execute($sql))
			{
				$e 	=	oci_error($sql);				
				return $e;
			}			
			//Armado del array
			while (($resultArr = oci_fetch_assoc($sql)) != false)
			{				
				array_push($arregloData, $resultArr);
			}
			return $arregloData;
		}

		function getConsultarNumer($params,$conexion)
		{
			/*Fecha creado: 15/12/2015
			Propósito: Generar el numerador del indicador CEED.
			Autor: DANE
			Parametros: 1.$params => array. 2.$conexion => String
			*/
			//Zona de declaraciones
			//$campos 	=	'"'.$params['entidad'].'"."COD_COL","'.$params['entidad'].'"."NOM_COL","'.$params['entidad'].'"."SECTOR","'.$params['entidad'].'"."DIR_COL","'.$params['entidad'].'"."TEL_COL","'.$params['entidad'].'"."EMAIL","'.$params['entidad'].'"."WEB_INST","'.$params['entidad'].'"."PREESCOLAR","'.$params['entidad'].'"."PRIMARIA","'.$params['entidad'].'"."SECUNDARIA","'.$params['entidad'].'"."MEDIA","'.$params['entidad'].'"."LATITUD","'.$params['entidad'].'"."LONGITUD","'.$params['entidad'].'"."CARACTER","'.$params['entidad'].'"."TOR_MAT"';
			$campos 	=	$params['entidad2'].'.numero_censo,'.$params['entidad2'].'.CODDEPTO,'.$params['entidad2'].'.CODMPIO,'.$params['entidad2'].'.fase_mes,'.$params['entidad2'].'.FECHA';			
			$agreg		=  	',(';
			$agreg		.=	'sum(TOTAL_OBRAS)) AS "TOTAL"';			
			$arregloData=	array();
			//Creación de la consulta
			//$qry 		= 	"SELECT $campos"." "."FROM"." ".$params['entidad2'];
			$qry 		= 	"SELECT $campos"."$agreg"." "."FROM"." ".$params['entidad2'];			
			//Condición 
			$condic		=	"WHERE"." "."NUMERO_CENSO=".$params['numcenso'];
			$condic 	.=	" "."AND"." "."fase_mes=".$params['fasemes']." "."AND"." "."coddepto=".$params['dpto']." "."AND"." "."codmpio="."'".$params['munic']."'";			
			//Agrupación			
			$agrupar	=	"GROUP BY"." ".$campos;
			//Ordenamiento
			$order 		=	"ORDER BY"." ".$campos;			
			//Armado de la consulta
			$qry	.=	" ".$condic." ".$agrupar." ".$order;			
			//Ejecución del query
			$sql 	=	oci_parse($conexion, $qry);
			if (!oci_execute($sql))
			{
				$e 	=	oci_error($sql);				
				return $e;
			}			
			//Armado del array
			while (($resultArr = oci_fetch_assoc($sql)) != false)
			{				
				array_push($arregloData, $resultArr);
			}
			return $arregloData;
		}
	}