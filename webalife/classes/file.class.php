<?php
/**
 * File manipulation library
 * User: ivans
 */
define('CMS_FILES_PATH', CMS_ROOT_DIR . 'files/stuff/');

class CFile
{
    private $path;

    /**
     * @param $file array
     * @return array
     */
    public static function upload($file)
    {
        global $db;
        // todo add catch of uploading errors

        // create directory
        @mkdir(CMS_FILES_PATH);
        $oldumask = umask(0);
        chmod(CMS_FILES_PATH, 0777);
        umask($oldumask);

        $ext = self::getExt($file['name']);
        $fileName = uniqid() . "." . $ext;
        move_uploaded_file($file['tmp_name'], CMS_FILES_PATH . $fileName);

        $db->sql("INSERT INTO " . PREFIX . "files SET
            file_type = '" . $file['type'] . "',
            file_name = '" . $file['name'] . "',
            file_size = '" . $file['size'] . "',
            file_cache_name = '" . $fileName . "',
            datetm = '" . time() . "'");

        return array(
            'ID' => $db->GetLastID(),
            'NAME' => $file['name'],
            'CACHE_NAME' => $fileName,
            'SIZE' => $file['size'],
            'EXT' => $ext
        );
    }

    /**
     * @param mixed $file
     * @param int $width
     * @param int $height
     * @param int $quality
     * @return array
     */
    public static function resizeImage($file, $width, $height, $quality = 100)
    {
        $fileId = $ext = $fname = null;
        if (isset($file['ID']) && isset($file['CACHE_NAME'])) {
            $fileId = $file['ID'];
            $fname = $file['CACHE_NAME'];
            $ext = self::getExt($file['CACHE_NAME']);
        }

        $from = CMS_FILES_PATH . $fname;
        $dstFName = substr($fname, 0, strlen($fname) - 4) . '_' . $width . '_' . $height . '.' . $ext;
        $to = CMS_RUNTIME_PATH . $dstFName;

        // размеры изображения
        $image_details = getimagesize($from);
        $x1 = $image_details[0];
        $y1 = $image_details[1];

        if ($x1 > $y1) {
            $y2 = $height;
            $x2 = floor($x1 / ($y1 / $height));

            if (@ImageCreateFromJPEG($from)) $i1 = @ImageCreateFromJPEG($from);
            if (@ImageCreateFromGIF($from)) $i1 = @ImageCreateFromGIF($from);
            if (@ImageCreateFromPNG($from)) $i1 = @ImageCreateFromPNG($from);
            $i2 = ImageCreateTrueColor($x2, $y2);
            $i3 = ImageCreateTrueColor($width, $height);

            $x3 = floor(($x2 - $width) / 2);

            ImageCopyResampled($i2, $i1, 0, 0, 0, 0, $x2, $y2, $x1, $y1);
            ImageCopyResampled($i3, $i2, 0, 0, $x3, 0, $width, $height, $width, $y2);
            ImageJpeg($i3, $to, $quality);

            ImageDestroy($i1);
            ImageDestroy($i2);
            ImageDestroy($i3);
        } else {
            $x2 = $width;
            $y2 = floor($y1 / ($x1 / $width));

            if (@ImageCreateFromJPEG($from)) $i1 = @ImageCreateFromJPEG($from);
            if (@ImageCreateFromGIF($from)) $i1 = @ImageCreateFromGIF($from);
            if (@ImageCreateFromPNG($from)) $i1 = @ImageCreateFromPNG($from);
            $i2 = ImageCreateTrueColor($x2, $y2);
            $i3 = ImageCreateTrueColor($width, $height);

            $y3 = floor(($y2 - $height) / 2);

            ImageCopyResampled($i2, $i1, 0, 0, 0, 0, $x2, $y2, $x1, $y1);
            ImageCopyResampled($i3, $i2, 0, 0, 0, $y3, $width, $height, $x2, $height);
            ImageJpeg($i3, $to, $quality);

            ImageDestroy($i1);
            ImageDestroy($i2);
            ImageDestroy($i3);
        }

        return array(
            'ID' => $fileId,
            'WIDTH' => $width,
            'HEIGHT' => $height,
            'SRC' => CMS_RUNTIME_URL . $dstFName,
            'EXT' => $ext
        );
    }

    public static function getFile($fileId)
    {

    }

    /**
     * Returns image file
     * @param $fileId
     * @param $width
     * @param $height
     * @return array
     * @throws Exception
     */
    public static function getImage($fileId, $width, $height)
    {
        global $db;
        $f = $db->sql("SELECT FILE_ID ID, FILE_CACHE_NAME CACHE_NAME FROM " . PREFIX . "files WHERE file_id = " . $fileId, 1);
        if (!isset($f['ID'])) {
            throw new Exception("File not found");
        }

        $fname = substr($f['CACHE_NAME'], 0, strlen($f['CACHE_NAME']) - 4) . '_' . $width . '_' . $height . '.' . self::getExt($f['CACHE_NAME']);
        if (!file_exists($fname)) {
            self::resizeImage($f, $width, $height);
        }

        return array(
            'ID' => $f['ID'],
            'WIDTH' => $width,
            'HEIGHT' => $height,
            'SRC' => CMS_RUNTIME_URL . $fname
        );
    }

    /**
     * Deletes file
     * @param $fileId
     */
    public static function delete($fileId)
    {
        global $db;
        $f = $db->sql("SELECT FILE_ID ID, FILE_CACHE_NAME CACHE_NAME FROM " . PREFIX . "files WHERE file_id = " . $fileId, 1);
        if (!isset($f['ID'])) {
            throw new Exception("File not found");
        }
        $db->sql("DELETE FROM " . PREFIX . "files WHERE file_id = " . $fileId);
        @unlink(CMS_FILES_PATH . $f['CACHE_NAME']);
    }

    private static function getExt($name)
    {
        return substr(strrchr($name, '.'), 1);
    }
}
