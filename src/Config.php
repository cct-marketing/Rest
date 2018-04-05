<?php

namespace CCT\Component\Rest;

use CCT\Component\Collections\ParameterCollection;

class Config extends ParameterCollection
{
    public const ENDPOINT = 'api.config.endpoint';

    public const URI_PREFIX = 'api.config.uri_prefix';

    public const API_KEY = 'api.config.api_key';

    public const API_VERSION = 'api.config.api_version';

    public const DEBUG = 'api.config.debug';

    public const CURL_CA_VERIFY = 'api.config.curl_ca_verfiy';

    //Not sure about these?
    public const EVENT_SUBSCRIBERS = 'api.config.event_subscribers';

    public const SERIALIZATION_HANDLERS = 'api.config.serialization_handlers';

    public const OBJECT_CONSTRUCTOR = 'api.config.object_constructor';

    public const RESPONSE_TRANSFORMERS = 'api.config.response_transformers';

    public const REQUEST_TRANSFORMERS = 'api.config.request_transformers';

    public const USE_DEFAULT_RESPONSE_TRANSFORMERS = 'api.config.use_default_response_transformers';

    public const RESPONSE_CLASS = 'api.config.response.class';

    public const FORM_NORMALIZER = 'api.config.form_normalizer';

    public const METADATA_DIRS = 'api.config.metadata_dirs';
}
