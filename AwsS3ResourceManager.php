<?php
/**
 * Created by PhpStorm.
 * User: danielfiebig
 * Date: 04/05/15
 * Time: 12:04
 */

namespace bdart\resourcemanager;

use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Service\Client;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Class AwsS3ResourceManager
 * @package bdart\resourcemanager
 */
class AwsS3ResourceManager extends Component implements ResourceManagerInterface
{

    /**
     * @var string Amazon access key
     */
    public $key;
    /**
     * @var string Amazon secret access key
     */
    public $secret;
    /**
     * @var string Amazon region
     */
    public $region;
    /**
     * @var
     */
    public $signature;
    /**
     * @var string Amazon Bucket
     */
    public $bucket;
    /**
     * @var \Aws\S3\S3Client
     */
    private $_client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach (['key', 'secret', 'bucket'] as $attribute) {
            if ($this->$attribute === null) {
                throw new InvalidConfigException(strtr('"{class}::{attribute}" cannot be empty.', [
                    '{class}' => static::className(),
                    '{attribute}' => '$' . $attribute
                ]));
            }
        }
        parent::init();
    }

    /**
     * Saves a file
     * @param \yii\web\UploadedFile $file the file uploaded. The [[UploadedFile::$tempName]] will be used as the source
     * file.
     * @param string $name the name of the file
     * @param string $folder the folder insight
     * @param array $options extra options for the object to save on the bucket. For more information, please visit
     * [[http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_putObject]]
     * @return \Guzzle\Service\Resource\Model
     */
    public function save($file, $name, $folder = null, $options = [])
    {
        $options = ArrayHelper::merge([
            'Bucket' => $this->bucket,
            'Key' => !empty($folder) ? $folder.'/'.$name : $name,
            'SourceFile' => $file,
            'ACL' => CannedAcl::PUBLIC_READ // default to ACL public read
        ], $options);

        $this->getClient()->putObject($options);
    }

    /**
     * Removes a file
     * @param string $name the name of the file to remove
     * @param string $folder the folder insight
     * @return boolean
     */
    public function delete($name, $folder = null)
    {
        $result = $this->getClient()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => !empty($folder) ? $folder.'/'.$name : $name
        ]);

        return $result['DeleteMarker'];
    }

    /**
     * Checks whether a file exists or not. This method only works for public resources, private resources will throw
     * a 403 error exception.
     * @param string $name the name of the file
     * @param string $folder the folder insight
     * @return boolean
     */
    public function fileExists($name, $folder = null)
    {
        $http = new \Guzzle\Http\Client();
        try {
            $response = $http->get($this->getUrl(!empty($folder) ? $folder.'/'.$name : $name))->send();
        } catch(ClientErrorResponseException $e) {
            return false;
        }
        return $response->isSuccessful();
    }

    /**
     * Returns the url of the file or empty string if the file does not exists.
     * @param string $name the key name of the file to access
     * @return string
     * @internal param string $folder the folder insight
     */
    public function getUrl($name)
    {
        return $this->getClient()->getObjectUrl($this->bucket, $name);
    }

    /**
     * Returns a S3Client instance
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = S3Client::factory([
                'key' => $this->key,
                'secret' => $this->secret,
                'region' => $this->region,
                'signature' => $this->signature
            ]);
        }
        return $this->_client;
    }
}