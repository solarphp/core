<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Marcelo Santos Araujo <marcelosaraujo@gmail.com>
 * 
 * @license LGPL
 * 
 * @version $Id: en_US.php 941 2006-03-22 21:09:09Z pmjones $
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'            => 'Português',
    'FORMAT_COUNTRY'             => 'Brasil',
    'FORMAT_CURRENCY'            => 'R$%s', // printf()
    'FORMAT_DATE'                => '%d/%m/%Y', // strftime(): Mar 19, 2005
    'FORMAT_TIME'                => '%R', // strftime: 12-hour am/pm
    
    // submit operations         
    'SUBMIT_CANCEL'              => 'Cancelar',
    'SUBMIT_CREATE'              => 'Criar',
    'SUBMIT_DELETE'              => 'Remover',
    'SUBMIT_EDIT'                => 'Editar',
    'SUBMIT_GO'                  => 'Ir!',
    'SUBMIT_LOGIN'               => 'Efetuar login',
    'SUBMIT_LOGOUT'              => 'Sair',
    'SUBMIT_NEXT'                => 'Próximo',
    'SUBMIT_PREVIEW'             => 'Prévia',
    'SUBMIT_PREVIOUS'            => 'Anterior',
    'SUBMIT_RESET'               => 'Reinicializar',
    'SUBMIT_SAVE'                => 'Salvar',
    'SUBMIT_SEARCH'              => 'Buscar',
    
    // controller actions
    'ACTION_BROWSE'              => 'Lista',
    'ACTION_READ'                => 'Ler',
    'ACTION_EDIT'                => 'Editar',
    'ACTION_ADD'                 => 'Adicionar',
    'ACTION_DELETE'              => 'Removar',
    
    // exception error messages  
    'ERR_CONNECTION_FAILED'      => 'Falha na conexão.',
    'ERR_EXTENSION_NOT_LOADED'   => 'Extensão não foi carregada.',
    'ERR_FILE_NOT_FOUND'         => 'Arquivo não encontrado.',
    'ERR_FILE_NOT_READABLE'      => 'Arquivo não pode ser lido ou não existe.',
    'ERR_METHOD_NOT_CALLABLE'    => 'Método não pode ser invocado.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Método não implementado.',
    
    // validation messages (used when validation fails)
    'VALID_ALPHA'                => 'Por favor, utilize apenas as letras A-Z.',
    'VALID_ALNUM'                => 'Por favor, utilize apenas as letras (A-Z) e números (0-9).',
    'VALID_BLANK'                => 'Este valor deve permanecer como não preenchido.',
    'VALID_EMAIL'                => 'Por favor, entre com um endereço de email válido.',
    'VALID_INKEYS'               => 'Por favor, selecione um valor diferente.',
    'VALID_INLIST'               => 'Por favor, selecione um valor diferente.',
    'VALID_INSCOPE'              => 'Este valor náo está contido em um escopo apropriado.',
    'VALID_INTEGER'              => 'Por favor, utilize apenas números inteiros.',
    'VALID_ISODATE'              => 'Por favor, entre com uma data no formato "yyyy-mm-dd".',
    'VALID_ISODATETIME'          => 'Por favor, entre com a data e o tempo (data-tempo) no formato
"yyyy-mm-ddThh:ii:ss".',
    'VALID_ISOTIME'              => 'Por favor, entre com o horário no formato "hh:ii:ss".',
    'VALID_MAX'                  => 'Por favor, entre com um valor menor.',
    'VALID_MAXLENGTH'            => 'Por favor, entre com uma string menor.',
    'VALID_MIN'                  => 'Por favor, entre com um valor maior.',
    'VALID_MINLENGTH'            => 'Por favor, entre com uma string maior.',
    'VALID_NOTZERO'              => 'Este valor não pode ser zero.',
    'VALID_NOTBLANK'             => 'Este valor não pode ser deixado em branco.',
    'VALID_SEPWORDS'             => 'Por favor, utilize apenas letras (A-Z), números (0-9),
underscores(_), e separadores.',
    'VALID_URI'                  => 'Por favor, entre com um endereço web válido.',
    'VALID_WORD'                 => 'Por favor, utilize apenas letras (A-Z), números (0-9), e underscores(_).',
    
    // success feedback messages
    'SUCCESS_FORM'               => 'Salvo.',
    
    // failure feedback messages  
    'FAILURE_FORM'               => 'Não-salvo; por favor corrija os erros apresentados.',
    'FAILURE_INVALID'            => 'Dado(s) inválidos.',
    
    // generic text      
    'TEXT_AUTH_USERNAME'         => 'Logado como',
    
    // generic form element labels  
    'LABEL_SUBMIT'               => 'Acão',
    'LABEL_HANDLE'               => 'Usuário',
    'LABEL_PASSWD'               => 'Senha',
);
?>
