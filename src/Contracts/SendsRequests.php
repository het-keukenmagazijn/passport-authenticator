<?php namespace Keukenmagazijn\PassportAuthenticator\Contracts;

interface SendsRequests
{
    public function get(string $endpoint, array $dataToSend = [], array $extraHeader = []): array;
    public function post(string $endpoint, array $dataToSend = [], array $extraHeader = []): array;
}
