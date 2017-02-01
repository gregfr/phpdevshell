<?php

    /**
     * Class PHPDS_view
     *
     * Allows you to add code to your view, when it's complicated enough so you want to
     * separate controler logic/view logic/view representation
     *
     * You may want to add links to javascript files or activate some specific template feature
     */
    class PHPDS_view extends PHPDS_dependant
    {
        /**
         * Contains the current active theme.
         *
         * @var string
         */
        public $theme;

        /**
         * Constructor
         *
         * @return mixed Parent's contructor result
         */
        public function construct()
        {
            $this->theme = $this->core->activeTemplate();

            return parent::construct();
        }

        /**
         * Looks up and returns data assigned to it in controller with $this->set();
         *
         * @param string $name
         * @return mixed
         */
        public function get($name)
        {
            if (!empty($this->core->toView->{$name})) {
                return $this->core->toView->{$name};
            } else {
                return $this->core->toView[$name];
            }
        }

        /**
         * Main execution point for class view.
         * Will execute automatically.
         */
        public function run()
        {
            $this->execute();
        }

        /**
         * This method should be overriden by your class, it's called after the controller execution (usually including
         * view rendering with Smarty), but before the page is actually built
         */
        public function execute()
        {
            // Your code here
        }
    }
