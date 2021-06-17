<?php


namespace Siarko\cli\io\output\termCapabilities;


class ColorCapability
{
    private $capabilityRaw = '';

    /**
     * ColorCapability constructor.
     * @param string $capabilityRaw
     */
    public function __construct(string $capabilityRaw)
    {
        $this->capabilityRaw = $capabilityRaw;
    }

    /**
     * @return string
     */
    public function getCapabilityRaw(): string
    {
        return $this->capabilityRaw;
    }

    /**
     * @return bool
     */
    public function isColor(): bool
    {
        return $this->isColor8() || $this->isColor16() || $this->isColor88() || $this->isColor256();
    }

    /**
     * @param bool $orMore
     * @return bool
     */
    public function isColor8(bool $orMore = true): bool
    {
        $flag = ($this->getCapabilityRaw() == '8');
        if ($orMore || !$flag) {
            $flag = $this->isColor16() || $this->isColor88() || $this->isColor256();
        }
        return $flag;
    }

    /**
     * @param bool $orMore
     * @return bool
     */
    public function isColor16(bool $orMore = true): bool
    {
        $flag = ($this->getCapabilityRaw() == '16');
        if ($orMore || !$flag) {
            $flag = $this->isColor88() || $this->isColor256();
        }
        return $flag;
    }

    /**
     * @param bool $orMore
     * @return bool
     */
    public function isColor88(bool $orMore = true): bool
    {
        $flag = ($this->getCapabilityRaw() == '88');
        if ($orMore || !$flag) {
            $flag = $this->isColor256();
        }
        return $flag;
    }

    /**
     * @return bool
     */
    public function isColor256(): bool
    {
        return ($this->getCapabilityRaw() == '256');
    }

}