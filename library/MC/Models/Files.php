<?php

class MC_Models_Files
{

    static $escapedFolders = array('.', '..');

    public function delete($source)
    {
        if (is_dir($source))
        {
            $dir = dir($source);

            while (FALSE !== ($dirName = $dir->read()))
            {
                if (in_array($dirName, self::$escapedFolders))
                {
                    continue;
                }

                if (is_dir(rtrim($source, '/') . '/' . $dirName))
                {

                    self::delete(rtrim($source, '/') . '/' . $dirName);
                }
                else
                {

                    @unlink(rtrim($source, '/') . '/' . $dirName);
                }
            }
            $dir->close();

            @rmdir(rtrim($source, '/'));
        }
        else
        {
            @unlink($source);
        }

        return true;

    }

    public function copy($source, $dest)
    {
        if (is_dir($source))
        {
            if (!file_exists($dest))
            {
                mkdir($dest, 0777, true);
            }

            $dir = dir($source);
            
            while (FALSE !== ($dirName = $dir->read()))
            {
                if (in_array($dirName, self::$escapedFolders))
                {
                    continue;
                }

                if (is_dir(rtrim($source, '/') . '/' . $dirName))
                {
                    self::copy(rtrim($source, '/') . '/' . $dirName, rtrim($dest, '/') . '/' . $dirName);
                }
                else
                {
                    copy(rtrim($source, '/') . '/' . $dirName, rtrim($dest, '/') . '/' . $dirName);
                }
            }
            $dir->close();
        }
        else
        {

            copy($source, $dest);
        }

    }

}