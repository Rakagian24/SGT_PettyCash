<?php

namespace Illuminate\Contracts\Auth {
    interface Guard {
        /**
         * @return \App\Models\WebUser|null
         */
        public function user();

        /**
         * @return bool
         */
        public function check();
    }
}
