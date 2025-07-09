<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Helper\FormKey;

use Magento\Framework\Encryption\Helper\Security;

class Validator
{
    public const FORM_KEY_FIELD = 'form_key';

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framwork\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->formKey = $formKey;
        $this->jsonHelper = $jsonHelper;
        $this->serializer = $serializer;
    }

    /**
     * Validate Form key
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function validate(\Magento\Framework\App\RequestInterface $request)
    {
        $formKey = $request->getParam(self::FORM_KEY_FIELD, null);
        if (!$formKey) {
            if ($request instanceof \Magento\Framework\App\PlainTextRequestInterface &&
                method_exists($request, 'getContent')
            ) {
                $jsonArray = $this->serializer->unserialize($request->getContent());
                $formKey = $this->extractFormKeyFromJsonArray($jsonArray);
            }
        }
        return $formKey && Security::compareStrings($formKey, $this->formKey->getFormKey());
    }

    /**
     * Extract Form Key from json data
     *
     * @param array $jsonArray
     * @return string|null
     */
    private function extractFormKeyFromJsonArray($jsonArray): ?string
    {
        if (isset($jsonArray[self::FORM_KEY_FIELD])) {
            return $jsonArray[self::FORM_KEY_FIELD];
        }
        foreach ($jsonArray as $item) {
            if (isset($item['name'], $item['value']) && $item['name'] === self::FORM_KEY_FIELD) {
                return $item['value'];
            }
        }
        return null;
    }
}
