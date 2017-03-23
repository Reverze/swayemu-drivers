<?php

/**
 * A class represent a domain
 */
class SWDomain
{
    /**
     * Domain name property
     * @var string
     */
    private $domainName = null;

    /**
     * Create a domain element
     * @param string $domain Domain name
     * @throws SWInvalidDomainException
     */
    public function __construct(string $domain = null)
    {
        if (empty($domain)){
            ;//do nothing here
        }
        else{
            $this->domainName = (string) $domain;
            if (!$this->isValidDomainName()){
                $this->domainName = null;
                throw new SWInvalidDomainException ("Domain name is invalid");
            }
        }
    }

    /**
     * Checks if domain name is valid
     * @return boolean Returns true if valid, returns false if invalid domain name
     * @throws SWEmptyException
     */
    protected function isValidDomainName()
    {
        if (!empty($this->domainName)){
            return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $this->domainName) //valid chars check
                && preg_match("/^.{1,253}$/", $this->domainName) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $this->domainName)   ); //length of each label
        }
        else{
            throw new SWEmptyException ("Domain name is not specified");
        }
    }

    /**
     * Checks if domain name is valid
     * @param string $domainName Domain name
     * @return boolean Returns true if valid, returns false if invalid domain name
     */
    public static function isValidDomain(string $domainName)
    {

        try{
            $domainObject = new SWDomain($domainName);
            return true; //no exception, so is valid
        }
        catch (SWInvalidDomainException $exception){
            return false;
        }
    }

}

?>
