<?php

declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Driver\ExtSoap;

use Phpro\SoapClient\Soap\Driver\ExtSoap\Generator\DummyMethodArgumentsGenerator;
use Phpro\SoapClient\Soap\Engine\DecoderInterface;
use Phpro\SoapClient\Soap\Engine\DriverInterface;
use Phpro\SoapClient\Soap\Engine\EncoderInterface;
use Phpro\SoapClient\Soap\Engine\Metadata\LazyInMemoryMetadata;
use Phpro\SoapClient\Soap\Engine\Metadata\ManipulatedMetadata;
use Phpro\SoapClient\Soap\Engine\Metadata\MetadataInterface;
use Phpro\SoapClient\Soap\Engine\Metadata\MetadataOptions;
use Phpro\SoapClient\Soap\HttpBinding\SoapRequest;
use Phpro\SoapClient\Soap\HttpBinding\SoapResponse;

class ExtSoapDriver implements DriverInterface
{
    /**
     * @var AbusedClient
     */
    private $client;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    public function __construct(
        AbusedClient $client,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        MetadataInterface $metadata
    ) {
    
        $this->client = $client;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->metadata = $metadata;
    }

    public static function createFromOptions(ExtSoapOptions $options): self
    {
        $client = AbusedClient::createFromOptions($options);

        return self::createFromClient($client, $options->getMetadataOptions());
    }

    public static function createFromClient(AbusedClient $client, MetadataOptions $metadataOptions = null): self
    {
        $metadataOptions = $metadataOptions ?: new MetadataOptions();

        $metadata = new LazyInMemoryMetadata(
            new ManipulatedMetadata(
                new ExtSoapMetadata($client),
                $metadataOptions->getMethodsManipulator(),
                $metadataOptions->getTypesManipulator()
            )
        );

        return new self(
            $client,
            new ExtSoapEncoder($client),
            new ExtSoapDecoder($client, new DummyMethodArgumentsGenerator($metadata)),
            $metadata
        );
    }

    public function decode(string $method, SoapResponse $response)
    {
        return $this->decoder->decode($method, $response);
    }

    public function encode(string $method, array $arguments): SoapRequest
    {
        return $this->encoder->encode($method, $arguments);
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function getClient(): AbusedClient
    {
        return $this->client;
    }
}
