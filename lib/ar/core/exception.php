<?php

	interface ar_exception { }

	class ar_exceptions {
		const NO_PATH_INFO     = 101;
		const UNKNOWN_ERROR    = 102;
		const HEADERS_SENT     = 103;
		const ACCESS_DENIED    = 104;
		const SESSION_TIMEOUT  = 105;
		const PASSWORD_EXPIRED = 106;
		const OBJECT_NOT_FOUND = 107;
		const DATABASE_EMPTY   = 108;
		const ILLEGAL_ARGUMENT = 109;
		const CONFIGURATION_ERROR = 110;
	}

	class ar_exceptionDefault extends Exception implements ar_exception { }

	class ar_exceptionIllegalRequest extends Exception implements ar_exception { }

	class ar_exceptionConfigError extends Exception implements ar_exception { }

	class ar_exceptionAuthenticationError extends Exception implements ar_exception { }
