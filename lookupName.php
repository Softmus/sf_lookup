<?php
//mode = ASSOC -> sc_select retornando indice de array como nome da coluna
//mode = NUM -> sc_select retornando indice de array numerico sequencial
//mode = LOOKUP_ASSOC -> sc_lookup retornando indice de array como nome da coluna
//mode = LOOKUP -> sc_lookup retornando indice de array como numerico sequencial
//conn = Vazio -> conexão atual ou o nome da conexão desejada, se for usar um nome de conexão,
//       deve criar no evento on_script_init ou on_execute o camando c_select(xx,"Select 1","ConexãoNome");
//       para o sc criar as vãriãveis e funções para esta conexão
//Retorna Array com resultado ou com valor===False  e $this->db_error=tipo de erro do banco
//Mais rápido que o sc_lookup e otimiza memória pelo fato de não guardar em variáveis globais
//limit = Número que define a quantidade de registros
//start = Número do Registro que se inicia a contagem	

Function sf_select($_select,$limit="0",$start="0",$_mode="ASSOC",$_conn="") {
  $_mode=$_mode==""?"ASSOC":$_mode;
  $_SESSION['scriptcase']['sc_sql_ult_comando'] = $_select;  
  $_SESSION['scriptcase']['sc_sql_ult_conexao'] = '';  
  $_ds=array(); 
  $_conn=$_conn==""?'$this->Db':'$this->Ini->nm_db_'.trim($_conn);
  IF (strtolower($_mode)=="assoc" || strtolower($_mode)=="lookup_assoc") 
  { 
    eval($_conn.'->SetFetchMode(ADODB_FETCH_ASSOC);'); 
  }else
  { 
    eval($_conn.'->SetFetchMode(ADODB_FETCH_NUM);'); 
  }
	
  IF ($limit == 0){ 
	   $_connexec='$_ok=($_rx=&'.$_conn.'->Execute($_select));';
	 } else {
       $_connexec='$_ok=($_rx=&'.$_conn.'->SelectLimit($_select,$limit,$start));';
	 }
	
  eval($_connexec);
	
  IF ($_ok)
  { 
    IF ( strtolower($_mode)=="lookup_assoc" || strtolower($_mode)=="lookup")
    {
            $_y = 0;  
            $_count = $_rx->FieldCount(); 
            while (!$_rx->EOF) 
            {
              IF (strtolower($_mode)=="lookup")  
              { 
                for ($_x = 0; $_x < $_count; $_x++) 
                {  
					if (isset($_rx->fields[$_x])){	
					   $_ds[$_y] [$_x] = $_rx->fields[$_x]; 
					}  
				
				}
              }ELSE
              {
					if ($_rx->fields <> ''){  
					  $_ds[$_y]  = $_rx->fields;
					}
				  
              } 
              $_y++;  
              $_rx->MoveNext(); 
            }  
            $_rx->Close(); 
            eval($_conn.'->SetFetchMode(ADODB_FETCH_NUM);'); 
		    $_ds = array_filter($_ds);
            RETURN $_ds;
     }ELSE
       eval($_conn.'->SetFetchMode(ADODB_FETCH_NUM);'); 
	   $_rx = array_filter($_rx);
       RETURN $_rx;
     {
     }
  }ELSE
  {
    $_ds=False; 
    eval('$this->db_error='.$_conn.'->ErrorMsg();');
    eval($_conn.'->SetFetchMode(ADODB_FETCH_NUM);'); 
    RETURN $_ds;
  }  
}

?>
