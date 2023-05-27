<?php
namespace App\Twig;

use App\Service\Utils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension{

    public function getFunctions(){
        return [
            new TwigFunction('adminSubmitButton', [$this, 'returnSubmitButton']),
            new TwigFunction('adminUploadTriggerButton', [$this, 'returnUploadTriggerButton']),
            new TwigFunction('notificationMsgShow', [$this, 'returnNotificationMsgShow']),
            new TwigFunction('showFullName', [$this, 'returnFullName']),
            new TwigFunction('showCountryEmoji', [$this, 'returnCountryEmoji']),

        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('abbreviateNumber', [$this, 'returnAbbreviatedNumber']),
        ];
    }

    public function returnSubmitButton(string $buttonLabel, string $additionalClasses='', array $attributes = [],
    string $class="btn btn-primary w-100 py-8 mb-4 rounded-2"){
        echo '<button type="submit" '.Utils::getArrayAsHTMLAttributes($attributes).'  class="'.$class.' '.$additionalClasses.'">
        '.$buttonLabel.' <span style="margin-left:10px" class="d-none spinner-border spinner-border-sm"
         role="status" aria-hidden="true"></span></button>';
    }

    public function returnAbbreviatedNumber($amount){
        echo Utils::getAbbreviatedAmount($amount);
    }

    public function returnNotificationMsgShow(string $message, $class="alert-primary"){
        echo '<div class="alert '.$class.'" role="alert">'.$message.'</div>';
    }
    public function returnCountryEmoji(string $countryCode){
        $country = Utils::getAllCountries($countryCode);
        echo Utils::maybeNullOrEmpty($country, 'emoji');
    }
    

    public function returnFullName($data, $firstNameLabel="firstName", $lastNameLabel="lastName"){
        $data = (array) $data;
        echo ($data[$firstNameLabel]. " ". $data[$lastNameLabel]);
    }

    public function returnUploadTriggerButton(string $label, array $attributes=[]){
        echo '<button '.Utils::getArrayAsHTMLAttributes($attributes).'  
        class="btn btn-secondary image-upload-trigger-btn">'.$label.'</button>';
    }

}