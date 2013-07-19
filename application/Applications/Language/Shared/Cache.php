<?php

    class App_Language_Shared_Cache {
        
        public static function start(){
            
            $db = Zend_Registry::get('db');
            
            
            $languages = $db->select()->from('language'); 
            $languages->where('lang_status = ?', 1);
            
            $langsRows = $db->fetchAll($languages);
            
            
            foreach ($langsRows as $row)
            {
                
                
                $phrases = $db->select()->from('language_phrases');
                $phrases->where(' lang_id = ? ',$row['lang_id']);
                
                $phrasesRows = $db->fetchAll($phrases);
                
                if($phrasesRows)
                {
                    $content = self::_buildFileContent($phrasesRows);
                
                    self::saveFile('Languages/'.$row['short_lang'].'.php',$content);
                }
                
            }
        }
        
        
        
        protected function _buildFileContent($data)
        {
            
            $file = '<?php '.PHP_EOL.'$lang = array();'.PHP_EOL;
            
            foreach ($data as $row)
            {
                $row = array_map('addslashes', $row);
                
                $file.='$lang["'.$row['phrase_name'].'"] = "'.$row['phrase_value'].'";'.PHP_EOL;
            }
            
            
            $file .= 'return $lang;';
            
            return $file;
            
        }
        
        
        protected function saveFile($fileName,$fileContent){
            
            file_put_contents($fileName, $fileContent);
            
        }


    }