<?php

include 'Db.php';

class KapModel
{
    private $_db;
    private $_n = 5;
    private $_currentUrl;
    private $_config;

    public function __construct($config = false)
    {
        if( ! $this->_config)
            $this->_config = include realpath(dirname(__FILE__)).'/../config.php';

        $this->_db = new KapDb($this->_config['data_dir'], $this->_config['db']);

        $this->_currentUrl = $this->_getUrl($this->_getServerURL());
    }


    public function updateKap($kap, $clear = false)
    {
        $kap = $this->_parseKap($kap);
        //$siteDomain = $this->_getDomainName($this->_getServerURL());
        $excluded = array();

        if( ! empty($kap))
        {
            if($clear)
                $this->_db->query("DELETE FROM `".$this->_db->getTableName('kap')."`");

            $data = array();
            foreach($kap as $link)
            {
                if( ! empty($link['queries']))
                {
                    foreach($link['queries'] as $query)
                    {
                        $data[] = array('phrase' => $query, 'url' => $link['url']);
                    }
                }
            }

            $this->_db->multiQuery('INSERT INTO '.$this->_db->getTableName('kap').' (`url`,`phrase`) VALUES (:url, :phrase)', $data);
        }

        return $excluded;
    }

    public function updateKapUrl($url, $old_url)
    {
        return $this->_db->query('UPDATE '.$this->_db->getTableName('kap').' SET `url`="'.$url.'" WHERE `url`="'.$old_url.'";');
    }

    public function updateLinksUrl($url, $old_url)
    {
        return $this->_db->query('UPDATE '.$this->_db->getTableName('links').' SET `target_url`="'.$url.'" WHERE `target_url`="'.$old_url.'";');
    }

    public function getKap($page, $limit = 1000, $filter = false)
    {
        $offset = $limit * ($page-1);

        if($filter)
            $where = ' WHERE `url` LIKE "%'.$filter.'%" OR `phrase` LIKE "%'.$filter.'%"';
        else
            $where = '';

        $kap = $this->_db->query('SELECT * FROM '.$this->_db->getTableName('kap').$where.' LIMIT '.$offset.', '.$limit);
        $count = $this->_db->query('SELECT count(id) AS cnt FROM '.$this->_db->getTableName('kap').$where.' LIMIT 1;');

        $result = array(
            'all' => $count['cnt'],
            'count' => count($kap),
            'kap' => array()
        );
        foreach($kap as $url)
        {
            $result['kap'][$url['url']][$url['id']] = $url['phrase'];
        }

        return $result;
    }

    public function getUrls($page=1, $ids = false, $limit = 1000, $filter = false)
    {
        $offset = $limit * ($page-1);



        $sql = 'SELECT * FROM `'.$this->_db->getTableName('links').'`';
        $sql_cnt = 'SELECT count(id) AS count FROM `'.$this->_db->getTableName('links').'`';

        if($filter)
        {
            $sql .= ' WHERE `page_url` LIKE "%'.$filter.'%" OR `target_url` LIKE "%'.$filter.'%" OR `phrase` LIKE "%'.$filter.'%"';
            $sql_cnt .= ' WHERE `page_url` LIKE "%'.$filter.'%" OR `target_url` LIKE "%'.$filter.'%" OR `phrase` LIKE "%'.$filter.'%"';
        }
        elseif($ids)
        {
            $sql .= ' WHERE `id` IN ('.$ids.')';
            $sql_cnt .= ' WHERE `id` IN ('.$ids.')';
        }

        $sql .= ' ORDER BY `id` DESC LIMIT '.$offset.', '.$limit;

        $links = $this->_db->query($sql);
        $all = $this->_db->query($sql_cnt.' LIMIT 1;');

        $result = array(
            'all' => 0,
            'count' => count($links),
            'links' => array()
        );

        if(isset($all['count']))
            $result['all'] = $all['count'];

        foreach($links as $link)
        {
            $result['links'][$link['page_url']][$link['id']] = array('phrase' => $link['phrase'], 'url' => $link['target_url']);
        }
        return $result;
    }

    public function removeAll($table, $ids)
    {
        $ids_arr = array_chunk(explode(',', trim($ids, ',')), 100);

        foreach($ids_arr as $ids)
        {
            $this->_db->query('DELETE FROM `'.$this->_db->getTableName($table).'` WHERE `id` IN ('.implode(",", $ids).')', array());
        }

        return true;
    }

    public function getLinks($type)
    {
        $n = $this->getParam('num_links', 5);
        $links = $this->_db->query('SELECT * FROM `'.$this->_db->getTableName('links').'` WHERE `page_url` = :url LIMIT '.$n, array(':url' => $this->_currentUrl));
        $num_links = count($links);

        // если ссылок меньше чем необходимо или вообще нет
        if($num_links < $n)
        {
            $limit = $n - $num_links;
            $kap = $this->_db->getRandomLinks($limit+5);

            if( ! empty($kap))
            {
                $new_links = array();
                // url на которые нельзя ставить ссылки с текущей страницы
                $excluded = $this->_getDisallowedUrls();

                if( ! empty($links))
                {
                    $old_links = $this->get_values($links, 'target_url');
                    $excluded = array_merge($excluded, $old_links);
                }

                foreach($kap as $url)
                {
                    if(in_array($url['url'], $excluded))
                        continue;

                    $excluded[] = $url['url'];

                    $phrase = $this->_db->query('SELECT id FROM `'.$this->_db->getTableName('links').'` WHERE `phrase` = :phrase LIMIT 1', array(':phrase' => $url['phrase']));

                    if( ! $phrase)
                    {
                        $new_links[] = array(
                            'page_url' => $this->_currentUrl,
                            'target_url' => $url['url'],
                            'phrase' => $url['phrase']
                        );
                    }

                    if(count($new_links) >= $limit)
                        break;
                }

                $links = array_merge($links, $new_links);

                $this->_db->multiQuery('INSERT INTO '.$this->_db->getTableName('links').' (`page_url`,`phrase`, `target_url`) VALUES (:page_url, :phrase, :target_url)', $new_links);
            }
        }

        return $this->render($links,$type);
    }

    public function getLink($id)
    {
        return $this->_db->query('SELECT * FROM `'.$this->_db->getTableName('links').'` WHERE `id`=:id LIMIT 1;', array(':id' => $id));
    }

    private function render($links, $type)
    {
        if( ! $links)
            return null;

        $delimeter = $this->getParam('delimeter', 'ul-li');

        $result = array();
        $start = '';
        $end = '';

        if($delimeter == 'ul-li')
        {
            $start = '<ul><li>';
            $end = '</li></ul>';

            $tag = '</li><li>';
        }
        elseif($delimeter == 'ol-li')
        {
            $start = '<ol><li>';
            $end = '</li></ol>';

            $tag = '</li><li>';
        }
        elseif($delimeter == 'br')
        {
            $tag = '<br />';
            $end = '<br />';
        }
        else
        {
            $tag = ', ';
        }

        if($type=='g'){
            $start='<div class="recently-block TrotskyVertical">'.'<p class="TrotskyHeadVertical">Смотрите также:</p>';
            $end='<div />';
            $tag = '';
        }

        foreach($links as $link)
        {
            if($link['target_url'] && $link['phrase']){
                if($type=='g'){
                    $result[] = '<a href="'.$link['target_url'].'">'.$link['phrase'].'</a>';
                }
                else{
                    $result[] = '<div class="Trotsky">'.'<a href="'.$link['target_url'].'">'.$link['phrase'].'</a>'.'</div>';
                }
            }
        }
        if($type=='g'){
            $result = $start.implode($tag, $result).$end;
        }else{
            $result = $this->getParam('title', '<ul style="margin-top: 10px"><li><div class="Trotsky TrotskyHead">Смотрите также:</div></li></ul>  ').$start.implode($tag, $result).$end;
        }

        $encoding = $this->getParam('encoding', 'UTF-8');
        if($encoding && $encoding != 'UTF-8')
            $result = iconv('UTF-8', $encoding, $result);

        if(!empty($result))
            return $result;
        else
            return '';
    }

    public function getParam($name, $default = false)
    {
        $value = $this->_db->getParam($name);

        return ($value) ? htmlspecialchars_decode($value) : $default;
    }

    public function setParam($name, $value)
    {
        return $this->_db->setParam($name, $value);
    }

    private function _getDisallowedUrls()
    {
        return array($this->_currentUrl);
    }

    private function _isUrl($url)
    {
        preg_match('/^(http(s)?:\/\/)?(www\.)?([^\/]+)/is', $url, $matches);

        return ! empty($matches[1]);
    }
    private function _getDomainName($url)
    {
        preg_match('/^(http(s)?:\/\/)?(www\.)?([^\/]+)/is', trim($url, '/'), $matches);

        return isset($matches[4]) ? $matches[4] : null;
    }

    private function _getServerURL()
    {
        $path = $_SERVER['REQUEST_URI'];
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];

        return "http://$host$path";
    }

    private function _parseKap($kap)
    {
        $kap = explode("\n", $kap);
        $cnt = -1;
        $urls = array();

        foreach($kap as $value)
        {
            $value = trim($value);
            if(empty($value))
                continue;

            // проверяем является ли строка url'ом
            if($this->_isUrl($value))
            {
                $cnt++;
                $urls[$cnt]['url'] = $value;
            }
            elseif(isset($urls[$cnt]['url']))
            {
                $urls[$cnt]['queries'][] = $value;
            }
        }

        return $urls;
    }

    private function _getUrl($url)
    {
        $data = parse_url($url);

        $skip_get_params = explode(',', $this->getParam('get_params', ''));
        $params = array();
        if(isset($data['query']))
        {
            parse_str($data['query'], $params);

            foreach($params as $key => $value)
            {
                if(! $value || in_array($key, $skip_get_params))
                    unset($params[$key]);
            }
            ksort($params);
        }

        $base_url = 'http://'.$data['host'].$data['path'];

        if($params)
            return $base_url.'?'.http_build_query($params);
        else
            return $base_url;
    }

    private function get_values($array, $fieldName)
    {
        $res = array();

        foreach($array as $value)
        {
            $res[] = $value[$fieldName];
        }

        return $res;
    }

}
