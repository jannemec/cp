<?php
/**
 * Facade for Diamac access
 *
 * @author nemec
 */
namespace model;

class System {
    use \Nette\SmartObject;
    /** @var \Nette\Database\Context */
    protected $dbf;
    
    /** @var \Adldap\AD $dbf */
    protected $ad;
          
    protected $connection;
    /**
     *
     * @param \Nette\Database\Context  $connection
     */
    public function __construct(\Nette\Database\Context $connection, \Adldap\AD $ad){
        $this->setConnection($connection);
        $this->ad = $ad;
    }
    
    /**
     * 
     * @param \Nette\Database\Context $dbf
     * @return model\System
     */
    public function setConnection(\Nette\Database\Context $connection) {
        $this->connection = $connection;
        return($this);
    }
    
    /**
     * 
     * @return \model\help\RockwellConnection
     */
    public function getConnection() {
        return($this->connection);
    }
    
    
    /**
     * Get params
     * @param string $id
     * @return \Nette\Database\Selection
     */
    public function getParams() {
        $params = $this->getConnection()->table('sys_param')->order('id');
        return($params);
    }
    
    /**
     * Get param
     * @param string $id
     * @return Array
     */
    public function getParamById($id = null) {
        $param = $this->getConnection()->table('sys_param')->where('id = ?', $id)->fetch();
        return($param);
    }    
    
    /**
     * Get param
     * @param string $id
     * @return String
     */
    public function getParam(string $type, string $key, string $name) {
        $param = $this->getConnection()->table('sys_param')->where('type = ? AND code = ? AND name = ?', $type, $key, $name)->fetch();
        return($param ? $param->value : null);
    } 
    
    /**
     * Get param
     * @param string $id
     * @return \Nette\Database\ActiveRow
     */
    public function getParamObj(string $type, string $key, string $name) {
        $param = $this->getConnection()->table('sys_param')->where('type = ? AND code = ? AND name = ?', $type, $key, $name)->fetch();
        return($param);
    }
    
    public function setParam(string $type, string $key, string $name, string $value, string $lmchid = '') {
        $param = $this->getParamObj($type, $key, $name);
        if ($param) {
            return($param->update(array('value' => strval($value))));
        } else {
            return($this->getConnection()->table('sys_param')->insert(array(
                      'type' => $type
                    , 'code' => $key 
                    , 'name' => $name 
                    , 'value' => $value
                    , 'lmdt' => new \DateTime()
                    , 'lmchid' => $lmchid
            )));
        }
    }
    
    public function delParam(string $type, string $key, string $name) {
        return($param = $this->getConnection()->table('sys_param')->where('type = ? AND code = ? AND name = ?', $type, $key, $name)->delete());
    }
    
    
    //==========================================================================
    // Document functions
    //==========================================================================
    /**
     * Get params
     * @param string $id
     * @return \Nette\Database\Selection
     */
    public function getParamDocs() {
        $params = $this->getConnection()->table('sys_paramdoc')->order('id');
        return($params);
    }
    
    /**
     * Get param
     * @param string $id
     * @return Array
     */
    public function getParamDocById($id = null) {
        $param = $this->getConnection()->table('sys_paramdoc')->where('id = ?', $id)->fetch();
        return($param);
    }    
    
    /**
     * Get param
     * @param string $id
     * @return String
     */
    public function getParamDoc(string $type, string $key, string $name) {
        $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $key, $name)->fetch();
        return($param ? $param->value : null);
    }
    public function getParamDocMulti(string $type, array $key, string $name) {
        if (count($key) == 1) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $key[0], $name)->fetch();
        } elseif (count($key) == 2) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? and code2 = ? AND name = ?', $type, $key[0], $key[1], $name)->fetch();
        } else {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? and code2 = ? and code3 = ? AND name = ?', $type, $key[0], $key[1], $key[2], $name)->fetch();
        }
        return($param ? $param->value : null);
    }
    
    /**
     * Get param
     * @param string $id
     * @return String
     */
    public function getParamDocRow(string $type, string $key, string $name) {
        $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $key, $name)->fetch();
        return($param);
    }
    public function getParamDocRowMulti(string $type, array $key, string $name) {
        if (count($key) == 1) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $key[0], $name)->fetch();
        } elseif (count($key) == 2) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? and code2 = ? AND name = ?', $type, $key[0], $key[1], $name)->fetch();
        } else {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? and code2 = ? and code3 = ? AND name = ?', $type, $key[0], $key[1], $key[2], $name)->fetch();
        }
        
        return($param);
    }
    
    /**
     * Get param
     * @param string $id
     * @return String
     */
    public function getParamDocTK(string $type, string $key) {
        $params = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ?', $type, $key)->order('name');
        return($params);
    } 
    public function getParamDocTKMulti(string $type, array $key) {
        if (count($key) == 1) {
            $params = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? ', $type, $key[0])->order('name');
        } elseif (count($key) == 2) {
            $params = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ?', $type, $key[0], $key[1])->order('name');
        } else {
            $params = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ? AND code3 = ?', $type, $key[0], $key[1], $key[2])->order('name');
        }
        
        return($params);
    }
    
    /**
     * Get param
     * @param string $id
     * @return \Nette\Database\ActiveRow
     */
    public function getParamDocObj(string $type, string $code, string $name) {
        $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $code, $name)->fetch();
        return($param);
    }
    public function getParamDocObjMulti(string $type, array $code, string $name) {
        if (count($code) == 1) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $code[0], $name)->fetch();
        } elseif (count($code) == 2) {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ? AND name = ?', $type, $code[0], $code[1], $name)->fetch();
        } else {
            $param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ? AND code3 = ? AND name = ?', $type, $code[0], $code[1], $code[2], $name)->fetch();
        }
        
        return($param);
    }
    
    public function setParamDoc(string $type, string $code, string $name, string $value, string $enctype = '', string $filename = '', string $lmchid = '') {
        $param = $this->getParamDocObj($type, $code, $name);
        if (empty($filename)) {
            $filename = $id;
        }
        if (is_null($enctype)) {
            $enctype = self::getMimeType($filename);
        }
        if ($param) {
            return($param->update(['value' => strval($value) , 'filename' => $filename, 'enctype' => $enctype]));
        } else {
            return($this->getConnection()->table('sys_paramdoc')->insert(array(
                      'type' => $type
                    , 'code' => $code 
                    , 'name' => $name
                    , 'value' => $value
                    , 'filename' => $filename
                    , 'enctype' => $enctype
                    , 'lmdt' => new \DateTime()
                    , 'lmchid' => $lmchid
            )));
        }
    }
    public function setParamDocMulti(string $type, array $code, string $name, string $value, string $enctype = '', string $filename = '', string $lmchid = '') {
        $param = $this->getParamDocObjMulti($type, $code, $name);
        if (empty($filename)) {
            $filename = $id;
        }
        if (is_null($enctype)) {
            $enctype = self::getMimeType($filename);
        }
        if ($param) {
            return($param->update(['value' => strval($value) , 'filename' => $filename, 'enctype' => $enctype]));
        } else {
            $in =[
                      'type' => $type
                    , 'code' => $code[0] 
                    , 'name' => $name
                    , 'value' => $value
                    , 'filename' => $filename
                    , 'enctype' => $enctype
                    , 'lmdt' => new \DateTime()
                    , 'lmchid' => $lmchid
            ];
            if (isset($code[1])) {
                $in['code2'] = $code[1];
            }
            if (isset($code[2])) {
                $in['code3'] = $code[2];
            }
            return($this->getConnection()->table('sys_paramdoc')->insert($in));
        }
    }
    
    public function delParamDoc(string $type, string $code, string $name) {
        return($param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $code, $name)->delete());
    }
    public function delParamDocMulti(string $type, array $code, string $name) {
        if (count($code) == 1) {
            return($param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND name = ?', $type, $code[0], $code[1], $name)->delete());
        } elseif (count($code) == 2) {
            return($param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ? AND name = ?', $type, $code[0], $code[1], $name)->delete());
        } else {
            return($param = $this->getConnection()->table('sys_paramdoc')->where('type = ? AND code = ? AND code2 = ? AND code3 = ? AND name = ?', $type, $code[0], $code[1], $code[2], $name)->delete());
        }
        
    }
    
    public function createParamDocFromFile(string $type, string $code, string $name, string $fileNamePath, string $enctype = '', string $lmchid = '') {
        $value = file_get_contents($fileNamePath);
        return($this->setParamDoc($type, $code, $name, $value, $enctype, basename($fileNamePath), $lmchid));
    }
    public function createParamDocFromFileMulti(string $type, array $code, string $name, string $fileNamePath, string $enctype = '', string $lmchid = '') {
        $value = file_get_contents($fileNamePath);
        return($this->setParamDocMulti($type, $code, $name, $value, $enctype, basename($fileNamePath), $lmchid));
    }
    
    
    private static function getMimeType($filepath) {
        if(!preg_match('/\.[^\/\\\\]+$/',$filepath)) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
        }
        switch(strtolower(preg_replace('/^.*\./','',$filepath))) {
        // START MS Office 2007 Docs
        case 'doc':
            return 'application/msword';
        case 'docx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        case 'docm':
            return 'application/vnd.ms-word.document.macroEnabled.12';
        case 'dotx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        case 'dotm':
            return 'application/vnd.ms-word.template.macroEnabled.12';
        case 'xlsx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        case 'xlsm':
            return 'application/vnd.ms-excel.sheet.macroEnabled.12';
        case 'xltx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
        case 'xltm':
            return 'application/vnd.ms-excel.template.macroEnabled.12';
        case 'xlsb':
            return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
        case 'xlam':
            return 'application/vnd.ms-excel.addin.macroEnabled.12';
        case 'pptx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        case 'pptm':
            return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
        case 'ppsx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
        case 'ppsm':
            return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
        case 'potx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.template';
        case 'potm':
            return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
        case 'ppam':
            return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
        case 'sldx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slide';
        case 'sldm':
            return 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
        case 'one':
            return 'application/msonenote';
        case 'onetoc2':
            return 'application/msonenote';
        case 'onetmp':
            return 'application/msonenote';
        case 'onepkg':
            return 'application/msonenote';
        case 'thmx':
            return 'application/vnd.ms-officetheme';
            //END MS Office 2007 Docs

        }
        if (file_exists($filepath)) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
        } else {
            return(null);
        }
    }
    
    public static function getStyleForDocType($doctype) {
        switch(trim($doctype)) {
            case 'doc':
            case 'docx':
            case 'dotx':
            case 'dotm':
            case 'rtf':
            case 'dot':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/vnd.ms-word.document.macroEnabled.12':
            case 'application/msword':
                return('iconfile doc');
                break;
            case 'xls':
            case 'xlsx':
            case 'xlsm':
            case 'xltx':
            case 'xlam':
            case 'xlsb':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.ms-excel.sheet.macroEnabled.12':
                return('iconfile xls');
                break;
            case 'pdf':
            case 'application/pdf':
                return('iconfile pdf');
                break;
            case 'jpg':
            case 'jpeg':
                return('iconfile jpg');
                break;
            case 'png':
                return('iconfile png');
                break;
            case 'gif':
                return('iconfile gif');
                break;
            case 'ppt':
            case 'pptx':
            case 'pptm':
            case 'potx':
            case 'potm':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                return('iconfile ppt');
                break;
            case 'txt':
            case 'log':
                return('iconfile txt');
                break;
            case 'xml':
                return('iconfile xml');
                break;
            case 'avi':
            case 'mov':
                return('iconfile avi');
                break;
            case 'zip':
                return('iconfile zip');
                break;
            case 'dwg':
                return('iconfile dwg');
                break;
            default: 
                return('iconfile file');
                break;
        }
    }
    
    static private $itnoFileTypes = [
        'certsupp' => 'Certifikát dodavatele'
        , 'bezplist' => 'Bezpečnostní list'
        , 'techlist' => 'Technický list'
    ];
    
    public static function getItnoFileTypes(): array {
        return(self::$itnoFileTypes);
    }
    
    public function getItnoFileTypeName(string $key): string {
        if (isset(self::$itnoFileTypes[$key])) {
            return(self::$itnoFileTypes[$key]);
        } else {
            return('');
        }
    }

    //==========================================================================
    // UserRelated functions
    //==========================================================================
    public function getUserFullName(string $username) {
        $user = $this->ad->getUser($username);
        if (empty($user)) {
            return(null);
        } else {
            return($user['displayname']);
        }
    }
    
    //==========================================================================
    // Planning
    //==========================================================================
    public function getFakeOpes(string $plgr, bool $deleted = false) {
        $opnos = $this->getConnection()->table('mwoope')->where('plgr = ? AND status <= ?', $plgr, $deleted ? '100' : '0');
        return($opnos);
    }
    
    public function getFakeOpe(int $id) {
        $opno = $this->getConnection()->table('mwoope')->get($id);
        return($opno);
    }
    
    public function addFakeOpno(string $plgr, string $description, \DateTime $stdt, \DateTime $fidt, string $user = '', string $orno = '') {
        return($this->getConnection()->table('mwoope')->insert(array(
                      'plgr' => $plgr
                    , 'description' => $description 
                    , 'status' => 0
                    , 'stdt' => $stdt
                    , 'fidt' => $fidt
                    , 'tm' => $fidt->diff($stdt)->format('%s')
                    , 'rgdt' => new \DateTime()
                    , 'lmdt' => new \DateTime()
                    , 'chid' => $user
                    , 'orno' => $orno
            )));
    }
    
    public function updateFakeOpno(int $id, array $params, string $user = '') {
        $opno = $this->getConnection()->table('mwoope')->get($id);
        if ($opno) {
            if (!isset($params['chid'])) {
                $params['chid'] = $user;
            }
            if (!isset($params['lmdt'])) {
                $params['lmdt'] = new \DateTime();
            }
            return($opno->update($params));
        } else {
            return(false);
        }
    }
    
    public function delFakeOpno(int $id, string $user = '') {
        $opno = $this->getConnection()->table('mwoope')->get($id);
        if ($opno) {
            return($opno->update(['status' => 99, 'chid' => $user, 'lmdt' => new \DateTime()]));
        } else {
            return(false);
        }
    }
    
    public function storeMailLog(string $name, string $text) {
        $mails = $this->getConnection()->table('mail_log');
        return($mails->insert(['name' => $name, 'content' => $text]));
    }
}