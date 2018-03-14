<?php

namespace CCT\Component\Rest;

use CCT\Component\Rest\Collection\ArrayCollection;

class Config extends ArrayCollection
{
    const ENDPOINT = 'api.config.endpoint';

    const URI_PREFIX = 'api.config.uri_prefix';

    const API_KEY = 'api.config.api_key';

    const API_VERSION = 'api.config.api_version';

    const DEBUG = 'api.config.debug';


    //Not sure about these?
    const EVENT_SUBSCRIBERS = 'api.config.event_subscribers';

    const SERIALIZATION_HANDLERS = 'api.config.serialization_handlers';

    const OBJECT_CONSTRUCTOR = 'api.config.object_constructor';

    const RESPONSE_TRANSFORMERS = 'api.config.response_transformers';

    const USE_DEFAULT_RESPONSE_TRANSFORMERS = 'api.config.use_default_response_transformers';

    const RESPONSE_CLASS = 'api.config.response.class';

    const FORM_NORMALIZER = 'api.config.form_normalizer';

    const METADATA_DIRS = 'api.config.metadata_dirs';
}
