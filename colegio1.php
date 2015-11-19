<?php
	/*Propósito: Carga de colegios a través del WebService.
	Uso: http://servidor/colegio/colegio1.php?latitud=xx&longitud=yy&distancia=zz
	Fecha creado: 26/10/2015
	Fecha actualizado: 27/10/2015
	Cambio realizado: Realizar cambio para validar envío de parámetros del WebService. Asignación del access-control a todo. Inclusión del módulo de seguridad.
	Fecha actualizado: 11/11/2015
	Cambio realizado: Actualizar la información con el regreso del campo COD_JORN, correspondiente al código de la jornada desde la entidad brindada en el array $vals => entidad2
	Autor: DANE
	DIRECCIÓN DE INVESTIGACIONES EN GEOESTADÍSTICA
	GRUPO DE TRABAJO DANE MODERNO
	*/
	header("content-type: application/json; charset=utf-8");
	header("access-control-allow-origin: *");
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '')
	{
		$vals				=	$_REQUEST;
		$vals['entidad']	=	'SICOLE_TABLA_SEDE';
		$vals['entidad2']	=	'SICOLE_TABLA_JORNADA';
		$metodo				=	'getConsultarColegio';
		//Información de la conexión a la BD
		$user 				=	'dane_gfueind';
		$clave				=	'sicole';
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
		echo "<br>Modo de uso: http://<servidor>/colegio1.php?longitud=x.x&latitud=y.y&distancia=z";
		echo "<br>Donde: <servidor>: IP del servidor donde se encuentra el webservice";
		echo "<br>x.x: Coordenada decimal correspondiente a la longitud";
		echo "<br>y.y: Coordenada decimal correspondiente a la latitud";
		echo "<br>z: distancia en metros";
	}

	class colegioModel
	{
		function getConsultarColegio($params,$conexion)
		{
			/*Fecha creado: 27/10/2015
			Propósito: Formar la consulta de tuplas desde la BD
			Autor: DANE
			Parametros: 1.$params => array. 2.$conexion => String
			Fecha actualizado: 11/11/2015
			Cambio realizado: Colocar la salida los siguientes campos: CARACTER, JORNADAS, TOR_MAT. Cuando la distancia sea 0 mts, ajustar la consulta, ya que las coordenadas son string. Consulta de la jornada Escolar de la entidad dada en entidad2, cruzada con entidad separadas por , cuando se encuentra más de 1.
			Fecha actualizado: 12/11/2015
			Cambio realizado: Fix de salida de información duplicada.
			Fecha actualizado: 12/11/2015
			Cambio realizado: Salida de la jornada ordenada de menor a mayor.
			Fecha actualizado: 18/11/2015
			Cambio realizado: Obtener la salida del Barrio y el código de la localidad
			Fecha actualizado: 19/11/2015
			Cambio realizado: Obtener la salida de la columna Nombre Institución, Genero. Obtener salida de los campos Preescolar, Primaria, Secundaria, Media, Total Matriculados separados por ','.Excluir el operador DISTINCT en cada agregado, para obtener las tuplas cuando sea 0.
			Observaciones: URL => http://localhost/colegio/colegio1.php?latitud=4.700606&longitud=-74.071503&distancia=100*/
			//Zona de declaraciones
			//$campos 	=	'"'.$params['entidad'].'"."COD_COL","'.$params['entidad'].'"."NOM_COL","'.$params['entidad'].'"."SECTOR","'.$params['entidad'].'"."DIR_COL","'.$params['entidad'].'"."TEL_COL","'.$params['entidad'].'"."EMAIL","'.$params['entidad'].'"."WEB_INST","'.$params['entidad'].'"."PREESCOLAR","'.$params['entidad'].'"."PRIMARIA","'.$params['entidad'].'"."SECUNDARIA","'.$params['entidad'].'"."MEDIA","'.$params['entidad'].'"."LATITUD","'.$params['entidad'].'"."LONGITUD","'.$params['entidad'].'"."CARACTER","'.$params['entidad'].'"."TOR_MAT"';
			$campos 	=	'"'.$params['entidad'].'"."COD_COL","'.$params['entidad'].'"."NOM_COL","'.$params['entidad'].'"."SECTOR","'.$params['entidad'].'"."DIR_COL","'.$params['entidad'].'"."TEL_COL","'.$params['entidad'].'"."EMAIL","'.$params['entidad'].'"."WEB_INST","'.$params['entidad'].'"."LATITUD","'.$params['entidad'].'"."LONGITUD","'.$params['entidad'].'"."CARACTER","'.$params['entidad'].'"."TOR_MAT"';
			$campos		.= 	',';
			$campos		.=	'"'.$params['entidad'].'"."SCANOMBRE","'.$params['entidad'].'"."COD_LOCAL"';
			$campos		.= 	',';
			$campos		.=	'"'.$params['entidad'].'"."NOM_INST","'.$params['entidad2'].'"."GENERO"';
			$agreg		=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."COD_JORN") AS "JORNADA"';
			//Niveles de educación desde entidad2
			$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."PREESCOLAR") AS "PREESCOLAR"';
			$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."PRIMARIA") AS "PRIMARIA"';
			$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."SECUNDARIA") AS "SECUNDARIA"';			
			$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."MEDIA") AS "MEDIA"';
			//Total de matriculados desde entidad2 
			$agreg		.=	',WM_CONCAT('.''.' '.'"'.$params['entidad2'].'"."TOT_MAT") AS "TOTAL_MATRICULADOS"';
			$arregloData=	array();
			//Creación de la consulta
			//$qry 		= 	"SELECT $campos"." "."FROM"." ".$params['entidad2'];
			$qry 		= 	"SELECT $campos"."$agreg"." "."FROM"." ".$params['entidad2'];			
			
			//Actualización de la consulta, cargar COD_JORN desde la entidad dada en el array $params => entidad2
			$qry 		.=	" "."INNER JOIN"." ".$params['entidad']." "."ON"." "."(".$params['entidad2']."."."COD_COL"."=".$params['entidad']."."."COD_COL".")";
			if ($params['distancia'] > 0)
			{
				$margLatitudMax	=	number_format($params['latitud']+($params['distancia']/108000),9,',','');
				$margLatitudMin	=	number_format($params['latitud']-($params['distancia']/108000),9,',','');
				$margLongitudMax=	number_format($params['longitud']+($params['distancia']/108000),9,',','');
				$margLongitudMin=	number_format($params['longitud']-($params['distancia']/108000),9,',','');				

				$condic 		=	"WHERE"." "."(".'"'.$params['entidad'].'"'.".".'"'."LATITUD".'"'." <='".$margLatitudMax."'"." "."AND"." ".'"'.$params['entidad'].'"'.".".'"'."LATITUD".'"'." >='".$margLatitudMin."'".")";
				if ($params['longitud'] > 0)
				{	
					$condic			.=	" "."AND"." "."(".'"'.$params['entidad'].'"'.".".'"'."LONGITUD".'"'." <='".$margLongitudMax."'"." "."AND"." ".'"'.$params['entidad'].'"'.".".'"'."LONGITUD".'"'." >='".$margLongitudMin."'".")";
				}
				else
				{
					$condic			.=	" "."AND"." "."(".'"'.$params['entidad'].'"'.".".'"'."LONGITUD".'"'." <='".$margLongitudMin."'"." "."AND"." ".'"'.$params['entidad'].'"'.".".'"'."LONGITUD".'"'." >='".$margLongitudMax."'".")";	
				}				
			}
			if ($params['distancia'] == 0)
			{
				//Reemplazamos . por ,
				$params['longitud']	=	str_replace('.', ',', $params['longitud']);
				$params['latitud']	=	str_replace('.', ',', $params['latitud']);				
				$condic = "WHERE"." ".'"'.$params['entidad'].'"'."."."LONGITUD='".$params['longitud']."'"." "."AND"." ".'"'.$params['entidad'].'"'."."."LATITUD='".$params['latitud']."'";
			}
			$agrupar	=	"GROUP BY"." ".$campos;			
			//Armado de la consulta
			$qry	.=	" ".$condic." ".$agrupar;			
			
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
				if ($resultArr['JORNADA'] != null)
				{
					$resultArr['JORNADA']	=	$resultArr['JORNADA']->load();					
				}
				if ($resultArr['PREESCOLAR'] != null)
				{
					$resultArr['PREESCOLAR']=	$resultArr['PREESCOLAR']->load();
				}
				if ($resultArr['PRIMARIA'] != null)
				{
					$resultArr['PRIMARIA']	=	$resultArr['PRIMARIA']->load();
				}
				if ($resultArr['SECUNDARIA'] != null)
				{
					$resultArr['SECUNDARIA']=	$resultArr['SECUNDARIA']->load();
				}
				if ($resultArr['MEDIA'] != null)
				{
					$resultArr['MEDIA']		=	$resultArr['MEDIA']->load();
				}
				if ($resultArr['TOTAL_MATRICULADOS'] != null)
				{
					$resultArr['TOTAL_MATRICULADOS']=	$resultArr['TOTAL_MATRICULADOS']->load();
				}
				array_push($arregloData, $resultArr);
			}
			return $arregloData;
		}
	}