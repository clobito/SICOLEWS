<?php
	class databaseClass
	{
		//Atributos
		private static $user 		=	'';
		private static $host		=	'';
		private static $pass 		=	'';
		private static $dataBase 	=	'';

		//Constructor
		function __construct($user, $host, $pass, $dataBase)
		{
			self::$user 	=	$user;
			self::$host 	=	$host;
			self::$pass 	=	$pass;
			self::$dataBase =	$dataBase;
		}

		//Método para realizar conexión en Oracle - user, pass, service
		public static function connect()
		{
			$conn = oci_pconnect(self::$user, self::$pass, self::$host."/".self::$dataBase);
			if (!$conn) {				
			   $m = oci_error();
			   //echo $m['message'], "\n";
			   return $m['message']."\n";
			   //exit;
			}
			else {
			   //print "Conectado";
			   return $conn;
			}
		}
	}