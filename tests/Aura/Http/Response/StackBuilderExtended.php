<?php
namespace Aura\Http\Request;

use Aura\Http\Request\ResponseStackFactory as ResponseStackFactory;

class ResponseBuilderExtended extends ResponseBuilder
{
    public function getResponse()
    {
        return $this->response;
    }
    
    public function setFileHandle($handle)
    {
        return $this->file_handle = $handle;
    }

    public function getFileHandle()
    {
        return $this->file_handle;
    }
}