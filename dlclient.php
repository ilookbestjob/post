<?php

//defined('TOS_INCLUDE') or die('No permissions');

/**
 * Класс для работы с API компании Деловые Линии
 * @author Golovlev Aleksey <golovlev.alex@gmail.com>
 */
class DLClient {

    /** @var string $session ID сессии */
    private $session;

    /** @var string $api_url  */
    private $api_url;

    /** @var string $appKey ключ API */
    private $appKey;

    /** @var string $mode режим работы API. 'json' или 'xml' */
    private $mode;

    /** @var string $in тип передаваемых данных. 'json', 'xml' или 'array' */
    private $in;

    /** @var string $out тип возвращаемых данных  'json', 'xml' или 'array' */
    private $out;

    /** @var array $flags дополнительные параметры, влияющие на работу */
    private $flags = array();

    /** @var array|string|object $result ответ API. Формат ответа указывается в переменной $out */
    public $result;

    /** @var array $log массив, хранящий запросы и их ответы */
    public $log = array();

    /** @var int $request_status статус последнего запроса. 0 - ошибка, 1 - выполнен, 2 - не определен */
    public $request_status;

    /** @var array $result_array ответ API в формате массива, не зависит от $out */
    private $result_array = array();

    /**
     * Создает экземпляр класса
     *
     * appKEY и sessionID будут автоматически подставляться ко всем запросам, дополнительно их отправлять не требуется<br>
     * Основным нюансом использования является режим работы класса $mode<br>
     * В целом, mode не важен - но желательно не использовать режим xml если на входе и выходе используется json<br>
     * Параметры $in и $out указывают тип данных, которые будут использоваться соответственно на входе и выходе<br>
     * Класс автоматически конвертирует запрос и ответ API в заданный формат<br>
     * <b>Важно!</b> При конвертации в xml тело запроса автоматически оборачивается в {<code>&lt;request&gt;...&lt;/request&gt;</code>}
     * <br> кроме случая произвольного запроса через $this->request()
     * В любой момент параметры можно изменить, используя функцию $this->setMode()
     *
     * @param string $appKey ключ API
     * @param string $api_url адрес обработчика https://api.dellin.ru
     * @param string $mode режим работы API. 'json' или 'xml'
     * @param string $in тип передаваемых данных. 'json', 'xml' или 'array'
     * @param string $out тип возвращаемых данных  'json', 'xml' или 'array'
     * @param array $flags [optional] дополнительные параметры, влияющие на работу
     *  - <b>RETURN_XML_AS_OBJECT</b> <i>(bool)</i> возвращаем XML как объект, а не как текст
     *  - <b>USE_XML_AS_OBJECT</b> <i>(bool)</i> принимает XML как объект, а не как текст
     *  - <b>SESSION_ID</b> <i>(string)</i> передача sessionID в явном виде при создании экземпляра класса
     *  - <b>DEBUG</b> <i>(bool)</i> если указан, то все запросы сохраняются в переменную $this->log
     *
     * @throws Exception Invalid DLClient
     * @example ../docs/examples/construct.php
     */
    function __construct($appKey, $api_url, $mode, $in, $out, $flags = array()) {
        $appKey ? $this->appKey = $appKey : die("No app key given");

        try {
            if (!isset($appKey) || $appKey == '') {
                throw new Exception('Invalid DLClient->appKey');
            } else {
                $this->mode = $mode;
            }
            if (!isset($appKey) || $appKey == '') {
                throw new Exception('Invalid DLClient->appKey');
            } else {
                $this->mode = $mode;
            }
            if (!in_array($mode, array('json', 'xml'))) {
                throw new Exception('Invalid DLClient->mode. Must be xml or json');
            } else {
                $this->mode = $mode;
            }
            if (!in_array($in, array('json', 'xml', 'array'))) {
                throw new Exception('Invalid DLClient->in. Must be xml, json or array');
            } else {
                $this->in = $in;
            }
            if (!in_array($out, array('json', 'xml', 'array'))) {
                throw new Exception('Invalid DLClient->out. Must be xml, json or array');
            } else {
                $this->out = $out;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $this->in = $in;
        $this->out = $out;
        $this->api_url = $api_url;
        if (is_array($flags)) {
            $this->flags = $flags;
        }
        if (isset($this->flags['SESSION_ID'])) {
            $this->session = $this->flags['SESSION_ID'];
        }

        $this->write_to_log(1, array('appKey' => $appKey, 'api_url' => $api_url, 'mode' => $mode), 'createClassInstance', array());
    }

    /**
     * Метод, отвечающий за выполнение запросов к API
     *
     * к запросу автоматически подставляются appKey и sessionID (если сессия создана)
     * Результат выполнения запроса хранится в переменной $this->result
     *
     * @param string $method_url адрес метода
     * @param array $params тело запроса
     * @return array итоговый запрос (пишем в лог)
     */
    private function wrapper($method_url, $params = array()) {
        $url = $this->api_url . $method_url . '.' . $this->mode;
        switch ($this->mode) {
            case 'json':
                // получаем исходные параметры
                switch ($this->in) {
                    case 'array':
                        $params["appKey"] = $this->appKey;
                        if (isset($this->session)) {
                            $params["sessionID"] = $this->session;
                        }
                        $content = json_encode($params);
                        break;
                    case 'json':
                        if (!is_array($params)) {
                            $temp = json_decode($params, TRUE);
                        }
                        $temp["appKey"] = $this->appKey;
                        if (isset($this->session)) {
                            $temp["sessionID"] = $this->session;
                        }
                        $content = json_encode($temp);
                        break;
                    case 'xml':
                        if (!is_array($params)) {
                            if ($this->flags['USE_XML_AS_OBJECT']) {
                                $json = json_encode($params);
                                $data = json_decode($json, TRUE);
                            } else {
                                $xml = simplexml_load_string($params);
                                $json = json_encode($xml);
                                $data = json_decode($json, TRUE);
                            }
                        } else {
                            $data = $params;
                        }
                        $data["appkey"] = $this->appKey;
                        if (isset($this->session)) {
                            $data["sessionid"] = $this->session;
                        }
                        $content = json_encode($data);
                        break;
                }
                // передаем запрос
                $opts = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type: application/json",
                        'content' => $content
                    )
                );
                $result = file_get_contents($url, false, stream_context_create($opts));

                $this->result_array = json_decode($result, TRUE);

                // получаем ответ
                switch ($this->out) {
                    case 'json':
                        $this->result = $result;
                        break;
                    case 'xml':
                        $xml = '<response>' . $this->arr2xml(json_decode($result, TRUE)) . '</response>';
                        if ($this->flags['RETURN_XML_AS_OBJECT']) {
                            $this->result = new SimpleXMLElement($xml, LIBXML_NOCDATA);
                        } else {
                            $this->result = $xml;
                        }
                        break;
                    case 'array':
                        $this->result = json_decode($result, TRUE);
                        break;
                }
                break;

            case 'xml':
                // получаем исходные параметры
                switch ($this->in) {
                    case 'array':
                        $params["appKey"] = $this->appKey;
                        if (isset($this->session)) {
                            $params["sessionID"] = $this->session;
                        }
                        $content = '<request>' . $this->arr2xml($params) . '</request>';
                        break;
                    case 'json':
                        if (!is_array($params)) {
                            $temp = json_decode($params, TRUE);
                        }
                        $temp["appKey"] = $this->appKey;

                        if (isset($this->session)) {
                            $temp["sessionID"] = $this->session;
                        }
                        $content = '<request>' . $this->arr2xml($temp) . '</request>';
                        break;
                    case 'xml':
                        if (is_array($params)) {
                            $params = '<request></request>';
                        } else {
                            if ($this->flags['USE_XML_AS_OBJECT']) {
                                $xml = $params;
                            } else {
                                $xml = new SimpleXMLElement($params);
                            }
                        }

                        $xml->addChild('appkey', $this->appKey);

                        if (isset($this->session)) {
                            $xml->addChild('sessionid', $this->session);
                        }
                        $content = $xml->asXML();

                        break;
                }
                $opts = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type: text/xml",
                        'content' => $content
                    )
                );
                $result = file_get_contents($url, false, stream_context_create($opts));

                $xml = simplexml_load_string($result);
                $json = json_encode($xml);
                $array = json_decode($json, TRUE);
                $this->result_array = $array;

                // получаем ответ
                switch ($this->out) {
                    case 'json':
                        $this->result = $json;
                        break;
                    case 'xml':
                        if ($this->flags['RETURN_XML_AS_OBJECT']) {
                            $this->result = new SimpleXMLElement($result, LIBXML_NOCDATA);
                        } else {
                            $this->result = $result;
                        }
                        break;
                    case 'array':
                        $this->result = $array;
                        break;
                }
                break;
        }

        return $params;
    }

    /**
     * выполнение произвольного запроса к API
     *
     * @param string $method_url адрес метода (к примеру <b>/v1/customers/orders</b>)
     * @param array|string|object $params тело запроса в формате $this->in
     * @return array|string|object данные в формате $this->out
     *
     * @example ../docs/examples/request.json.php JSON
     * @example ../docs/examples/request.xml.php XML
     */
    public function request($method_url, $params = array()) {
        $this->wrapper($method_url, $params);
        $this->write_to_log(2, $params, 'request: ' . $method_url, $this->result);
        return $this->result;
    }

    /**
     * для дебага
     *
     * @param int $status 1=ok, 0=error, 2=no_info
     * @param array $request
     * @param array $answer
     */
    private function write_to_log($status, $request, $method, $answer) {
     /*   if ($this->flags['DEBUG']) {
            $log = array(
                'status' => $status,
                'time' => date('Y-m-d H:m:i') . ' ' . microtime(),
                'method' => $method,
                'request' => $request,
                'answer' => $answer
            );
            array_push($this->log, $log);
        }*/
    }

    /**
     * переключаем параметры mode, in, out на новые значения
     *
     * @param string $type mode/in/out
     * @param string $value mode->(json/xml) in->(array/json/xml) out->(array/json/xml)
     */
    public function setMode($type, $value) {
        switch ($type) {
            case 'mode':
                $this->mode = $mode;
                break;
            case 'in':
                $this->in = $in;
                break;
            case 'out':
                $this->out = $out;
                break;
        }
    }

    /**
     * возвращает текущее значении параметра mode/in/out
     *
     * раз есть сеттер, будет и геттер
     *
     * @param string $type mode/in/out
     * @return string
     */
    public function getMode($type) {
        switch ($type) {
            case 'mode':
                $ret = $this->mode;
                break;
            case 'in':
                $ret = $this->in;
                break;
            case 'out':
                $ret = $this->out;
                break;
        }
        return $ret;
    }

    /**
     * преобразует массив в строку, подготовленную для преобразования в xml
     *
     * преобразование осуществляется функцией: <br>
     * $xml_obj = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);<br>
     * альтернативно: <br>
     * $xml_obj = new SimpleXMLElement($xml_string, LIBXML_NOCDATA);<br>
     * для проверки типа массива используется $this->is_assoc();
     *
     * @param array $arr
     * @return string
     */
    public function arr2xml($arr) {
        $xml = '';
        if ($this->is_assoc($arr)) {
            foreach ($arr as $key => $val) {
                if (is_array($val)) {
                    if ($this->is_assoc($val)) {
                        $xml .= '<' . $key . '>' . $this->arr2xml($val) . '</' . $key . '>';
                    } else {
                        foreach ($val as $item) {
                            $xml .= '<' . $key . '>' . $this->arr2xml($item) . '</' . $key . '>';
                        }
                    }
                } else {
                    $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
                }
            }
        }
        return $xml;
    }

    /**
     * проверка, является ли массив ассоциативным
     *
     * @param array $array
     * @return bool TRUE если ассоциативный
     */
    public function is_assoc($array) {
        return (is_array($array) && (count($array) == 0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))))));
    }

    /**
     * подает на вывод следующее:
     *
     * <code>&lt;pre&gt; print_r($mixed); &lt;/pre&gt;</code>
     *
     * @param mixed $mixed
     * @param bool $XML_SCREENING [optional]<br>Если TRUE, то заменяем ('<', '>') на html-сущности<br> Юзабельно для вывода XML в читаемом виде
     */
    public function pre($mixed, $XML_SCREENING = false) {
        echo '<pre>';
        if ($XML_SCREENING) {
            $txt = print_r($mixed, TRUE);
            echo str_replace('<', '&lt;', str_replace('>', '&gt;', $txt));
        } else {
            print_r($mixed);
        }
        echo '</pre>';
    }

    /**
     * устанавлиаем в SESSIONID произвольное значение
     *
     * Альтернатива авторизации по логину/паролю<br>
     * Но вообще лучще указывать сессию при создании класса<br>
     * Основной кейс - если нужно переключиться на работу с другим пользователем<br>
     * Но даже в этом случае лучше создавать отдельный экземпляр
     *
     * @param string $sessionID кэп
     */
    public function setSessionId($sessionID) {
        $this->session = $sessionID;
    }

    /**
     * Авторизация
     *
     * проверяет существование живой сессии, если такой нет - создает <br>
     * сохраняет id сессии в переменную session<br>
     *
     * @param array|string|object $params параметры запроса в формате
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category General
     * @category Authorization
     * @example ../docs/examples/login.php запрос
     * @example ../docs/examples/login_A.php ответ - успех
     * @example ../docs/examples/login_A_E.php ответ - ошибка
     * @link http://dev.dellin.ru/api/customers/auth/ подробная документация
     */
    public function login($params) {
        $this->getSessionInfo();
        $execute = $this->wrapper('/v1/customers/login', $params);
        if (!isset($this->result_array['errors'])) {
            $this->session = $this->result_array['sessionID'];
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'login', $this->result);
        return $this->result;
    }

    /**
     * Закрытие сессии для клиентской авторизации
     *
     * Запрос не требует передачи параметров<br>
     * <b>В ответе всегда</b><br>
     * {<br>
     * &nbsp;&nbsp;&nbsp;"answer": "ok"<br>
     * }<br>
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category General
     * @category Authorization
     * @link http://dev.dellin.ru/api/customers/methods/ подробная документация
     * @example ../docs/examples/logout.php
     */
    public function logout() {
        $execute = $this->wrapper('/v1/customers/logout');
        if ($this->result_array['answer'] == 'ok') {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'logout', $this->result);
        return $this->result;
    }

    /**
     * Получение информации о сессии авторизации
     *
     * Запрос не требует передачи параметров
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getSessionInfo.php запрос
     * @example ../docs/examples/getSessionInfo_A.php ответ
     * @category General
     * @category Get info
     * @link http://dev.dellin.ru/api/customers/methods/ подробная документация
     */
    public function getSessionInfo() {
        $execute = $this->wrapper('/v1/customers/session_info');
        $this->write_to_log(2, $execute, 'getSessionInfo', $this->result);
        return $this->result;
    }

    /**
     * Получить список доступных контрагентов (counteragents)
     *
     * Запрос не требует передачи параметров<br>
     * Запрос требует предварительную авторизацию<br>
     * Одна учётная запись в личном кабинете может иметь доступ к данным сразу нескольких контрагентов. <br>
     * Поэтому для некоторых запросов может возникнуть необходимость переключения контрагента <br>
     * (например, для оформления заявок или получения данных по взаиморасчётам).<br>
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Adress Book
     * @category Get info
     * @link http://dev.dellin.ru/api/customers/methods/ подробная документация
     * @example ../docs/examples/getCounteragents.php запрос
     * @example ../docs/examples/getCounteragents_A.php ответ
     */
    public function getCounteragents() {
        $execute = $this->wrapper('/v1/customers/counteragents');
        $this->write_to_log(2, $execute, 'getCounteragents', $this->result);
        return $this->result;
    }

    /**
     * Получить список контрагентов (counteragents) из адресной книги
     *
     * Запрос не требует передачи параметров<br>
     * Запрос требует предварительную авторизацию<br>
     * Это просто список из адресной книги - не те КА, по которым проведены заявки!
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Adress Book
     * @category Get info
     * @example ../docs/examples/getCounteragentsList.php запрос
     * @example ../docs/examples/getCounteragentsList_A.php ответ
     * @link http://dev.dellin.ru/api/customers/book/ подробная документация
     */
    public function getCounteragentsList() {
        $execute = $this->wrapper('/v1/customers/book/counteragents');
        $this->write_to_log(2, $execute, 'getCounteragentsList', $this->result);
        return $this->result;
    }

    /**
     * Добавление и редактирование контрагентов
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Adress Book
     * @category Create or Update info
     * @example ../docs/examples/createOrUpdateCounteragents.php Запрос юрлицо
     * @example ../docs/examples/createOrUpdateCounteragents_2.php Запрос физлицо
     * @example ../docs/examples/createOrUpdateCounteragents_A.php Ответ - успех
     * @example ../docs/examples/createOrUpdateCounteragents_A_E.php Ответ - ошибка
     * @link http://dev.dellin.ru/api/customers/book/ подробная документация
     */
    public function createOrUpdateCounteragents($params) {
        $execute = $this->wrapper('/v1/customers/book/counteragents/update', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'createOrUpdateCounteragents', $this->result);
        return $this->result;
    }

    /**
     * Получить список адресов
     *
     * Запрос не требует передачи параметров<br>
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getCounteragentsAdress.php запрос
     * @example ../docs/examples/getCounteragentsAdress_A.php ответ
     * @link http://dev.dellin.ru/api/customers/book/
     * @category Adress Book
     * @category Get info
     */
    public function getCounteragentsAdress($params) {
        $execute = $this->wrapper('/v1/customers/book/addresses', $params);
        $this->write_to_log(2, $execute, 'getCounteragentsAdress', $this->result);
        return $this->result;
    }

    /**
     * Добавление и редактирование адресов
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/createOrUpdateCounteragentsAdress.php запрос
     * @example ../docs/examples/createOrUpdateCounteragentsAdress_A.php ответ - успех
     * @example ../docs/examples/createOrUpdateCounteragentsAdress_A_E.php ответ - ошибка
     * @category Adress Book
     * @category Create or Update info
     * @link http://dev.dellin.ru/api/customers/book/
     */
    public function createOrUpdateCounteragentsAdress($params) {
        $execute = $this->wrapper('/v1/customers/book/addresses/update', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'createOrUpdateCounteragentsAdress', $this->result);
        return $this->result;
    }

    /**
     * Получить список контактных лиц и телефонов для адреса
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getAdressContactsList.php запрос
     * @example ../docs/examples/getAdressContactsList_A.php ответ
     * @category Adress Book
     * @category Get info
     * @link http://dev.dellin.ru/api/customers/book/
     */
    public function getAdressContactsList($params) {
        $execute = $this->wrapper('/v1/customers/book/address', $params);
        $this->write_to_log(2, $execute, 'getAdressContactsList', $this->result);
        return $this->result;
    }

    /**
     * Добавление и редактирование контактных лиц
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/createOrUpdateContacts.php запрос - добавление
     * @example ../docs/examples/createOrUpdateContacts_2.php запрос - редактирование
     * @example ../docs/examples/createOrUpdateContacts_A.php ответ - успех
     * @example ../docs/examples/createOrUpdateContacts_A_E.php ответ - ошибка
     * @category Adress Book
     * @category Create or Update info
     * @link http://dev.dellin.ru/api/customers/book/
     */
    public function createOrUpdateContacts($params) {
        $execute = $this->wrapper('/v1/customers/book/contacts/update', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'createOrUpdateContacts', $this->result);
        return $this->result;
    }

    /**
     * Добавление и редактирование контактных телефонов
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/createOrUpdateContactsPhone.php запрос - добавление
     * @example ../docs/examples/createOrUpdateContactsPhone_2.php запрос - редактирование
     * @example ../docs/examples/createOrUpdateContactsPhone_A.php ответ - успех
     * @example ../docs/examples/createOrUpdateContactsPhone_A_E.php ответ - ошибка
     * @category Adress Book
     * @category Create or Update info
     * @link http://dev.dellin.ru/api/customers/book/
     */
    public function createOrUpdateContactsPhone($params) {
        $execute = $this->wrapper('/v1/customers/book/phones/update', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'createOrUpdateContactsPhone', $this->result);
        return $this->result;
    }

    /**
     * Удаление объектов из адресной книги
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/deleteContacts.php запрос
     * @example ../docs/examples/deleteContacts_A.php ответ
     * @category Adress Book
     * @category Delete info
     * @link http://dev.dellin.ru/api/customers/book/
     */
    public function deleteContacts($params) {
        $execute = $this->wrapper('/v1/customers/book/delete', $params);
        $this->write_to_log(2, $execute, 'deleteContacts', $this->result);
        return $this->result;
    }

    /**
     * Статус доставки груза по номеру заказа, накладной или заявки
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/tracker.php запрос
     * @category Get public info
     * @link http://dev.dellin.ru/api/public/tracker/
     */
    public function tracker($params) {
    	$execute = $this->wrapper('/v2/public/tracker', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'tracker', $this->result);
//        print_r($this);
        return $this->result;
    }

    /**
     * Запрос поиска накладных по неполным данным
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/tracker_advanced.php запрос
     * @example ../docs/examples/tracker_advanced_A.php ответ - успех
     * @example ../docs/examples/tracker_advanced_A_E.php ответ - ошибка
     * @category Get public info
     * @link http://dev.dellin.ru/api/public/tracker_advanced/
     */
    public function tracker_advanced($params) {
        $execute = $this->wrapper('/v1/public/tracker_advanced', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'tracker_advanced', $this->result);
        return $this->result;
    }

    /**
     * Расчёт стоимости перевозки
     *
     * Запрос требует предварительную авторизацию для учета индивидуальных скидок
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/calculator.php запрос
     * @example ../docs/examples/calculator_2.php запрос c учетом скидок
     * @example ../docs/examples/calculator_A_E.php ответ - ошибка
     * @link http://dev.dellin.ru/api/public/calculator/
     * @category Get public info
     */
    public function calculator($params) {
        $execute = $this->wrapper('/v1/public/calculator', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'calculator', $this->result);
        return $this->result;
    }

    /**
     * Журнал отправок
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getOrdersList.php запрос
     * @category Get private info
     * @link http://dev.dellin.ru/api/customers/order/
     */
    public function getOrdersList($params) {
        $execute = $this->wrapper('/v2/customers/orders', $params);
        if (!isset($this->result_array['errors'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'getOrdersList', $this->result);
        return $this->result;
    }

    /**
     * Печатные формы документов
     *
     * Запрос требует предварительную авторизацию<br>
     * <b>"docUid": "0xad339ac31247666145816f2aeb4935ab"</b> uid документа<br>
     * <b>"mode": "bill"</b> тип формы <br>
     * возможные варианты:
     *  - "bill" - счет
     *  - "order" - накладная
     *  - "invoice" - счет-фактура
     *  - "cashOrder" - ПКО
     *  - одно из значений, полученных по ключу available_docs в накладной из запроса getOrdersList
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getPrintableDocuments.php запрос
     * @example ../docs/examples/getPrintableDocuments_A.php ответ
     * @category Get private info
     * @category Get documents
     * @link http://dev.dellin.ru/api/customers/order/
     */
    public function getPrintableDocuments($params) {
        $execute = $this->wrapper('/v2/customers/orders/printable', $params);
        $this->write_to_log(2, $execute, 'getPrintableDocuments', $this->result);
        return $this->result;
    }

    /**
     * Отправка скан-копий накладных на выдачу груза является отдельной услугой
     *
     * Запрос требует предварительную авторизацию<br>
     * доступно только по согласованию с персональным менеджром.
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getPrintableGiveoutOrders.php запрос
     * @example ../docs/examples/getPrintableGiveoutOrders_A.php ответ
     * @category Get private info
     * @category Get documents
     * @link http://dev.dellin.ru/api/customers/order/
     */
    public function getPrintableGiveoutOrders($params) {
        $execute = $this->wrapper('/v2/customers/orders/giveout_orders', $params);
        $this->write_to_log(2, $execute, 'getPrintableGiveoutOrders', $this->result);
        return $this->result;
    }

    /**
     * Печатная форма заявки на доставку от адреса
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getPrintableRequest.php запрос
     * @example ../docs/examples/getPrintableRequest_A.php ответ
     * @category Get private info
     * @category Get documents
     * @link http://dev.dellin.ru/api/customers/order/
     */
    public function getPrintableRequest($params) {
        $execute = $this->wrapper('/v2/customers/request/pdf', $params);
        $this->write_to_log(2, $execute, 'getPrintableRequest', $this->result);
        return $this->result;
    }

    /**
     * Печатная форма заявки на доставку до адреса
     *
     * Запрос требует предварительную авторизацию
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getPrintableRequestSf.php запрос
     * @example ../docs/examples/getPrintableRequestSf_A.php ответ
     * @category Get private info
     * @category Get documents
     * @link http://dev.dellin.ru/api/customers/order/
     */
    public function getPrintableRequestSf($params) {
        $execute = $this->wrapper('/v2/customers/request_sf/pdf', $params);
        $this->write_to_log(2, $execute, 'getPrintableRequestSf', $this->result);
        return $this->result;
    }

    /**
     * Заявка на доставку от адреса / предзаказ
     *
     * Запрос требует предварительную авторизацию<br>
     * {@link http://dev.dellin.ru/api/customers/request/ подробнее}
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Orders
     * @link http://dev.dellin.ru/api/customers/request/
     */
    public function createRequest($params) {
        $execute = $this->wrapper('/v1/customers/request', $params);
        if (!isset($this->result_array['errors']) || !isset($this->result_array['error'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'doRequest', $this->result);
        return $this->result;
    }

    /**
     * Заявка на доставку до адреса клиента
     *
     * Запрос требует предварительную авторизацию<br>
     * {@link http://dev.dellin.ru/api/customers/sfrequest/ подробнее}
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Orders
     * @link http://dev.dellin.ru/api/customers/sfrequest
     */
    public function createRequestSf($params) {
        $execute = $this->wrapper('/v1/customers/sfrequest', $params);
        if (!isset($this->result_array['errors']) || !isset($this->result_array['error'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'doRequestSf', $this->result);
        return $this->result;
    }

    /**
     * Взаиморасчёты. Движение денежных средств в разрезе накладных и оплаченных счетов.
     *
     * Запрос требует предварительную авторизацию<br>
     * Просмотр идет для ТЕКУЩЕГО выбранного контрагента<br>
     * Список доступных контрагентов получать через getCounteragentsList<br>
     * @param array|string|object $params данные в формате переменной $this->in
     * <pre>{
      "appKey":    "ваш ключ приложения",
      "sessionID": "ваш текущий sessionID",
      "cauid":     "00000000-0000-0000-0000-000000000001", // не обязательное поле, от имени какого контрагента запрос (подробнее в разделе: Авторизация, полезные методы)
      "month": 12, //месяц, за который нужны взаиморасчёты
      "year": 2013 //год, за который нужны взаиморасчёты
      }</pre>
     *
     * @param array|string|object $params параметры запроса в формате $this->in
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getMutualCalculationsList.php запрос
     * @example ../docs/examples/getMutualCalculationsList_A.php запрос c учетом скидок
     * @example ../docs/examples/getMutualCalculationsList_A_E.php ответ - ошибка
     * @category Get private info
     * @link http://dev.dellin.ru/api/customers/payments/ документация
     */
    public function getMutualCalculationsList($params) {
        $execute = $this->wrapper('/v1/customers/mutual_calculations', $params);
        if (!isset($this->result_array['error'])) {
            $this->request_status = 1;
        } else {
            $this->request_status = 0;
        }
        $this->write_to_log($this->request_status, $execute, 'getMutualCalculationsList', $this->result);
        return $this->result;
    }

    /**
     * Каталоги
     *
     *  - <b>countries</b> Справочник стран
     *  - <b>cities</b> Справочник городов
     *  - <b>streets</b> Список улиц с кодами КЛАДР
     *  - <b>places</b> Список населённых пунктов с кодами КЛАДР
     *  - <b>services</b> Дополнительные услуги для доставки груза от/до адреса
     *  - <b>request_services</b> Дополнительные услуги
     *  - <b>request_delivery_types</b> Варианты доставки груза
     *  - <b>opf_list</b> Организационно-правовые формы
     *  - <b>payer_types</b> Типы плательщиков
     *  - <b>payments_types</b> Варианты оплаты
     *  - <b>load_types</b> Варианты загрузки машины
     *  - <b>statuses</b> Справочник статусов заказов
     *  - <b>packages</b> Виды упаковок
     *
     * @param string $catalog название необходимого каталога
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @example ../docs/examples/getCatalog.php запрос
     * @example ../docs/examples/getCatalog_A.php запрос c учетом скидок
     * @category Get public info
     * @link http://dev.dellin.ru/api/public/tables/
     */
    public function getCatalog($catalog) {
        $execute = $this->wrapper('/v1/public/' . $catalog);
        $this->write_to_log(2, $execute, 'getCatalog', $this->result);
        return $this->result;
    }

    /**
     * Получение списка терминалов с графиком работы
     *
     * Запрос не требует передачи параметров<br>
     * ВНИМАНИЕ. Не рекомендуется обращаться  к этому справочнику
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Get public info
     * @link http://dev.dellin.ru/api/public/terminals/
     */
    public function getTerminalsList() {
        $execute = $this->wrapper('/v3/public/terminals');
        $this->write_to_log(2, $execute, 'getTerminalsList', $this->result);
        return $this->result;
    }

    /**
     * Получение списка терминалов с графиком работы
     *
     * Запрос не требует передачи параметров
     *
     * @return array|string|object возвращает данные в формате $this->out
     *
     * @category Get public info
     * @link http://dev.dellin.ru/api/public/terminals/
     */
    public function getTerminalsListJSON() {
        $execute = $this->wrapper('/v2/public/terminals');
        $this->write_to_log(2, $execute, 'getTerminalsListJSON', $this->result);
        return $this->result;
    }

}
