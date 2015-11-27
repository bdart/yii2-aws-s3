<?php
/**
 * Created by PhpStorm.
 * User: danielfiebig
 * Date: 04/05/15
 * Time: 12:06
 */

namespace bdart\resourcemanager;


/**
 * Interface ResourceManagerInterface
 * @package bdart\resourcemanager
 */

/** @noinspection PhpUndefinedClassInspection */
interface ResourceManagerInterface
{
    /**
     * Saves a file
     * @param \yii\web\UploadedFile $file the file uploaded
     * @param string $name the name of the file
     * @param string $folder the folder insight
     * @param array $options
     * @return boolean
     */
    public function save($file, $name, $folder = null, $options = []);

    /**
     * Removes a file
     * @param string $name the name of the file to remove
     * @param string $folder the folder insight
     * @return boolean
     */
    public function delete($name, $folder = null);

    /**
     * Checks whether a file exists or not
     * @param string $name the name of the file
     * @param string $folder the folder insight
     * @return boolean
     */
    public function fileExists($name, $folder = null);

    /**
     * Returns the url of the file or empty string if the file does not exist.
     * @param string $name the name of the file
     * @return string
     */
    public function getUrl($name);

}