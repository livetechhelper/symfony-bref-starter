<?php


namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RequestHelperService
 * @package App\Service
 */
class RequestHelperService
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(RequestStack $requestStack, ParameterBagInterface $parameterBag)
    {
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
    }

    public function getCountryFromCurrentRequest(): string
    {
        $request = $this->getCurrentRequest();
        return $request->headers->get($this->getParam('request.country_field')) ?? '--';
    }

    public function getRegionFromCurrentRequest(): ?string
    {
        $request = $this->getCurrentRequest();
        return $request->headers->get($this->getParam('request.region_field')) ?? null;
    }

    public function getCityFromCurrentRequest(): ?string
    {
        $request = $this->getCurrentRequest();
        return $request->headers->get($this->getParam('request.city_field')) ?? null;
    }

    public function getIpAddressFromCurrentRequest(): ?string
    {
        $request = $this->getCurrentRequest();
        return $request->headers->get($this->getParam('request.ip_address_field')) ?? null;
    }

    public function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     * @return RequestHelperService
     */
    public function setRequestStack(RequestStack $requestStack): RequestHelperService
    {
        $this->requestStack = $requestStack;
        return $this;
    }

    public function getParam(string $name): \UnitEnum|float|int|bool|array|string|null
    {
        return $this->getParameterBag()->get($name);
    }

    /**
     * @return ParameterBagInterface
     */
    public function getParameterBag(): ParameterBagInterface
    {
        return $this->parameterBag;
    }
}