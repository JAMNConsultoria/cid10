<?php
require 'conn.php';
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());
$app->response()->header('Content-Type', 'application/json;charset=utf-8');
$app->get('/', function() use($app) {
    $app->response->setStatus(200);
    echo "Bemvindo à API de dados da CID-10";
}); 
# GET .../cid10/api/listas/
$app->group('/listas', function () use ($app) {
        $app->get('/capitulos/:numcap','listaCapitulos');
        $app->get('/grupos/:numcap','listaGrupos');	
		$app->get('/categorias/:catinic/:catfim','listaCategorias');	
		$app->get('/subcategorias/:cat','listaSubCategorias');			
});

$app->notFound(function(){echo 'humm...not sure what you mean';});
$app->run();

#lista de capitulos
function listaCapitulos($numcap) {
	$where ="";
	if(strtoupper($numcap)!='ALL'){
		$where .= " WHERE numcap=:numcap";
	}    
	$campos ="numcap,catinic,catfim,descricao,descrabrev";
	$sql  = " SELECT {$campos} FROM cid10_capitulos";
    $sql .= $where;
    $sql .= " ORDER BY numcap";
     #echo $sql;   
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		if(strtoupper($numcap)!='ALL'){
		   $stmt->bindParam(':numcap', $numcap, PDO::PARAM_STR); 
		}
		$stmt->execute();	
		$capitulos = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		if($capitulos){
			echo '{"capitulos": ' . json_encode($capitulos,JSON_NUMERIC_CHECK) . '}';	
		}else{
			echo '{"warning":{"text":"dado não encontrado."}}';
		}                                
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

#lista Grupos
function listaGrupos($numcap) {
	$where ="";
	if(strtoupper($numcap)!='ALL'){
		$where .= " WHERE numcap=:numcap";
	}
	$campos ="numcap,catinic,catfim,descricao,descrabrev";
	$sql  = " SELECT {$campos} FROM cid10_grupos";
    $sql .= $where;
    $sql .= " ORDER BY numcap,catinic,catfim";
    #echo $sql;    
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		if(strtoupper($numcap)!='ALL'){
		   $stmt->bindParam(':numcap', $numcap, PDO::PARAM_STR); 
		}
		$stmt->execute();	
		$grupos = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		if($grupos){
			echo '{"grupos": ' . json_encode($grupos,JSON_NUMERIC_CHECK) . '}';	
		}else{
			echo '{"warning":{"text":"dado não encontrado."}}';
		}                                
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

#lista Categorias
function listaCategorias($catinic,$catfim) {
	$where="";
	$where .= " WHERE cat between :catinic and :catfim";
	$campos ="cat,classif,descricao,descrabrev,refer,excluidos";
	$sql  = " SELECT {$campos} FROM cid10_categorias";
    $sql .= $where;
    $sql .= " ORDER BY cat";
    #echo $sql;    
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
 	    $stmt->bindParam(':catinic', $catinic, PDO::PARAM_STR); 
		$stmt->bindParam(':catfim', $catfim, PDO::PARAM_STR); 		
		$stmt->execute();	
		$categorias = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		if($categorias){
			echo '{"categorias": ' . json_encode($categorias,JSON_NUMERIC_CHECK) . '}';	
		}else{
			echo '{"warning":{"text":"dado não encontrado."}}';
		}                                
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

#lista Categorias
function listaSubCategorias($cat) {
	$cat="$cat%";
	$where="";
	$where .= " WHERE subcat LIKE :cat";
	$campos ="subcat,classif,restrsexo,causaobito,descricao,descrabrev,refer,excluidos";
	$sql  = " SELECT {$campos} FROM cid10_subcategorias";
    $sql .= $where;
    $sql .= " ORDER BY subcat";
    #echo $sql;    
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
 	    $stmt->bindParam(':cat', $cat, PDO::PARAM_STR); 
		$stmt->execute();	
		$subcategorias = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		if($subcategorias){
			echo '{"subcategorias": ' . json_encode($subcategorias,JSON_NUMERIC_CHECK) . '}';	
		}else{
			echo '{"warning":{"text":"dado não encontrado."}}';
		}                                
		#echo verifica_json();

	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}




#verifica saída da função json_encode
function verifica_json(){
		if (json_last_error() == 0) { 
		$msg = '- Nao houve erro! O parsing foi perfeito'; 
	}else{	
		$msg = 'Erro!</br>'; 
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH: 
				$msg .= ' - profundidade maxima excedida';
				break;
			case JSON_ERROR_STATE_MISMATCH: 
				$msg .= ' - state mismatch'; 
				break; 
			case JSON_ERROR_CTRL_CHAR: 
				$msg .= ' - Caracter de controle encontrado'; 
				break; 
			case JSON_ERROR_SYNTAX: 
				$msg .=' - Erro de sintaxe! String JSON mal-formada!'; 
				break; 
			case JSON_ERROR_UTF8: 
				$msg .=' - Erro na codificação UTF-8'; 
				break; 
			default: 
				$msg .=' – Erro desconhecido'; 
				break; 
		}
	}
	return $msg;
}
?> 