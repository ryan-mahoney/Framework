Embedded Managers
=================

When a project utilizes a document oriented data store, such as MongoDB, the functionality of nesting documents within other documents is commonly used.  

For example, if you have a manager for “categories” it may have an embedded document that stores “sub-categories”.

To achieve this functionality, FMF and Semantic-CM have a rich set if logic and user interfaces for facilitating this use case.  That being said, it is at times confusing because there are many step.


Steps to Embedding Manager
++++++++++++++++++++++++++

You will need two managers, one acting as a “parent” or container and one as a embedded “child”.

There are a few things to be mindful of:

- PC: the name of the parent storage collection, for example: “categories”
- HF: the name of the field in the parent that will hold embedded documents, for example: “subcategories”
- EM: the name of the embedded manager, for example: “subcategories”


Link the child from the parent
******************************

.. code-block:: php

    <?php
    namespace Manager;

    class categories {
        private $field = false;
        public $collection = 'categories';
        public $title = 'Categories';
        public $titleField = 'title';
        public $singular = 'Category';
        public $description = '{{count}} categories';
        public $definition = '....';
        public $acl = ['content', 'admin', 'superadmin'];
        public $tabs = ['Main', 'SEO'];
        public $icon = 'checkmark sign';
        public $category = 'Content';
        public $after = 'function';
        public $function = 'ManagerSaved';
        public $storage = [
            'collection' => 'categories',
            'key' => '_id'
        ];

        function titleField () {
            return array(
                'name' => 'title',
                'label' => 'Title',
                'required' => true,
                'display' => 'InputText'
            );
        }

        public function subcategoryField() {
            return [
                'name' => 'subcategory',  //this field will hold the embedded manager
                'label' => 'Sub Categories',
                'required' => false,
                'display'    =>    'Manager',
                'manager'    => 'subcategories'
            ];
        }

        public function formPartial () {
            $partial = <<<'HBS'
                    {{#Form}}{{/Form}}
                    <div class="top-container">
                        {{#DocumentHeader}}{{/DocumentHeader}}
                        {{#DocumentTabs}}{{/DocumentTabs}}
                    </div>

                    <div class="bottom-container">
                        <div class="ui tab active" data-tab="Main">
                            {{#DocumentFormLeft}}
                                {{#FieldLeft title Title required}}{{/FieldLeft}}
                                <!-- "subcategory" is the field in the parent -->
                                <!-- "subcategories" is the name of the embedded manager php file -->
                                {{#FieldEmbedded field="subcategory" manager="subcategories"}}
                                {{{id}}}
                            {{/DocumentFormLeft}}                 
                        </div>
                    </div>
                </form>
    HBS;
            return $partial;
        }
    }



Designate the child as embedded
*******************************

.. code-block:: php

    <?php
    namespace Manager;

    class subcategories {
        private $field = false;
        public $collection = 'categories';
        public $title = 'Subcategories';
        public $titleField = 'title';
        public $singular = 'Subcategory';
        public $description = '4 subcategories';
        public $definition = '';
        public $acl = ['content', 'admin', 'superadmin'];
        public $icon = 'browser';
        public $category = 'Content';
        public $after = 'function';
        public $function = 'embeddedUpsert';     //important!  the function name is different 
        public $embedded = true;                 //important!  it is designated at embedded
        public $storage = [
            'collection' => 'categories',        //important! it refers to the parent manager's collection
            'key' => '_id'
        ];

        public function __construct ($field=false) {
            $this->field = $field;
        }

        function titleField () {
            return [
                'name'        => 'title',
                'label'        => 'Title',
                'required'    => false,
                'display'    => 'InputText'
            ];
        }
        
        public function tablePartial () {
            $partial = <<<'HBS'
                <!-- "Subcategories" is just a label -->
                {{#EmbeddedCollectionHeader label="Subcategories"}}
                
                <!-- "subcategory" is the name of the field in the parent manager -->
                {{#if subcategory}}
                    <table class="ui table manager segment">
                        <thead>
                            <tr><th>Title</th></tr>
                            <tr><th class="trash">Delete</th></tr>
                        </thead>
                        <tbody>

                            <!-- "subcategory" is the name of the field in the parent manager -->
                            {{#each subcategory}}
                                <tr data-id="{{dbURI}}">
                                    <td>{{title}}</td>
                                    <td><div class="manager trash ui icon button"><i class="trash icon small"></i></div></td>
                                </tr>
                            {{/each}}
                        </tbody>
                    </table>
                {{else}}

                    <!-- "subcategory" is a label that says what type of thing will be added -->
                    {{#EmbeddedCollectionEmpty singular="Subcategory"}}
                {{/if}}
    HBS;
            return $partial;
        }

        public function formPartial () {
            $partial = <<<'HBS'
                {{#EmbeddedHeader}}
                {{#FieldFull title Title}}{{/FieldFull}}
                {{{id}}}
                {{#EmbeddedFooter}}
    HBS;
            return $partial;
        }
    }