<?php
/**

*@copyright : Satyendra Pandey

*@author	 : Satyendra Pandey  < pandeysatyendra870@gmail.com >

*

* All Rights Reserved.

* Proprietary and confidential :  All information contained herein is, and remains

* the property of Satyendra Pandey  and his partners.

* Unauthorized copying of this file, via any medium is strictly prohibited.

*

*/

 



namespace app\models;



use Yii;

use app\models\Feed;

use app\models\User;



use yii\helpers\ArrayHelper;





/**

* This is the model class for table "tbl_payment".

*



    * @property integer $id


    * @property string $title


    * @property string $key


    * @property string $value


    * @property integer $state_id


    * @property integer $type_id


    * @property string $create_time


    * @property integer $created_by_id





* === Related data ===

    
* @property User $createdBy

    

*/





class Payment extends \app\components\TActiveRecord

{


	public  function __toString()

	{

		return (string)$this->title;

	}


	const STATE_INACTIVE 	= 0;

	const STATE_ACTIVE	 	= 1;

	const STATE_DELETED 	= 2;



	public static function getStateOptions()

	{

		return [

				self::STATE_INACTIVE		=> "New",

				self::STATE_ACTIVE 			=> "Active" ,

				self::STATE_DELETED 		=> "Deleted",

		];

	}

	public function getState()

	{

		$list = self::getStateOptions();

		return isset($list [$this->state_id])?$list [$this->state_id]:'Not Defined';



	}

	public function getStateBadge()

	{

		$list = [

				self::STATE_INACTIVE 		=> "secondary",

				self::STATE_ACTIVE 			=> "success" ,

				self::STATE_DELETED 		=> "danger",

		];

		return isset($list[$this->state_id])?\yii\helpers\Html::tag('span', $this->getState(), ['class' => 'badge badge-' . $list[$this->state_id]]):'Not Defined';

	}

    public static function getActionOptions()

    {

        return [

            self::STATE_INACTIVE => "Deactivate",

            self::STATE_ACTIVE => "Activate",

            self::STATE_DELETED => "Delete"

        ];

    }



	
	public static function getTypeOptions()

	{

		return ["TYPE1","TYPE2","TYPE3"];

			
	}



	
 	public function getType()

	{

		$list = self::getTypeOptions();

		return isset($list [$this->type_id])?$list [$this->type_id]:'Not Defined';



	}

	
	
	
	
public function beforeValidate()

	{

		if($this->isNewRecord)

		{

	
			if ( empty( $this->create_time )){ $this->create_time = date( 'Y-m-d H:i:s');}

	
			if ( empty( $this->created_by_id )){ $this->created_by_id = self::getCurrentUser();

            }

	
		}else{

	
	
	
		}

		return parent::beforeValidate();

	}






	/**

	* @inheritdoc

	*/

	public static function tableName()

	{

		return '{{%payment}}';

	}




	/**

	* @inheritdoc

	*/

	public function rules()

	{

		return [
            [['title', 'key', 'value', 'created_by_id'], 'required'],
            [['state_id', 'type_id', 'created_by_id'], 'integer'],
            [['create_time'], 'safe'],
            [['title'], 'string', 'max' => 1024],
            [['key', 'value'], 'string', 'max' => 255],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['title', 'key', 'value'], 'trim'],
            [['state_id'], 'in', 'range' => array_keys(self::getStateOptions())],
            [['type_id'], 'in', 'range' => array_keys (self::getTypeOptions())]
        ];

	}



	/**

	* @inheritdoc

	*/





	public function attributeLabels()

	{

		return [

		
		    'id' => Yii::t('app', 'ID'),

		
		    'title' => Yii::t('app', 'Title'),

		
		    'key' => Yii::t('app', 'Key'),

		
		    'value' => Yii::t('app', 'Value'),

		
		    'state_id' => Yii::t('app', 'State'),

		
		    'type_id' => Yii::t('app', 'Type'),

		
		    'create_time' => Yii::t('app', 'Create Time'),

		
		    'created_by_id' => Yii::t('app', 'Created By'),

		
		];

	}




    /**

    * @return \yii\db\ActiveQuery

    */

    public function getCreatedBy()

    {

    	return $this->hasOne(User::className(), ['id' => 'created_by_id']);

    }



    public static function getHasManyRelations()

    {

    	$relations = [];




    	$relations['feeds'] = [

            'feeds',

            'Feed',

            'model_id'

        ];

		return $relations;

	}

    public static function getHasOneRelations()

    {

    	$relations = [];


		$relations['created_by_id'] = ['createdBy','User','id'];


		return $relations;

	}



	public function beforeDelete() {

	    if (! parent::beforeDelete()) {

            return false;

        }

        //TODO : start here


		return true;

	}

	

  	public function beforeSave($insert)

    {

        if (! parent::beforeSave($insert)) {

            return false;

        }

        //TODO : start here

        

        return true;

    }

    public function asJson($with_relations=false)

	{

		$json = [];


			$json['id'] 	= $this->id;



			$json['title'] 	= $this->title;



			$json['key'] 	= $this->key;



			$json['value'] 	= $this->value;



			$json['state_id'] 	= $this->state_id;



			$json['type_id'] 	= $this->type_id;



			$json['create_time'] 	= $this->create_time;



			$json['created_by_id'] 	= $this->created_by_id;



			if ($with_relations)

		    {


				// createdBy


				$list = $this->createdBy;



				if ( is_array($list))

				{

					$relationData = [];

					foreach( $list as $item)

					{

						$relationData [] 	= $item->asJson();

					}

					$json['createdBy'] 	= $relationData;

				}

				else

				{

					$json['createdBy'] 	= $list;

				}


			}

		return $json;

	}

	

	
	

    public static function addTestData($count = 1)

    {

        $faker = \Faker\Factory::create();

        $states = array_keys(self::getStateOptions());

        for ($i = 0; $i < $count; $i ++) {

            $model = new self();

            $model->loadDefaultValues();

						$model->title = $faker->text(10);
			$model->key = $faker->text(10);
			$model->value = $faker->text(10);
			$model->state_id = $states[rand(0,count($states))];
			$model->type_id = 0;

        	$model->save();

        }

	}

    public static function addData($data)

    {

    	if (self::find()->count() != 0)

    	{

            return;

        }

        

        $faker = \Faker\Factory::create();

        foreach( $data as $item) {

            $model = new self();

            $model->loadDefaultValues();


                    

                    	$model->title = isset($item['title'])?$item['title']:$faker->text(10);

                   
                    

                    	$model->key = isset($item['key'])?$item['key']:$faker->text(10);

                   
                    

                    	$model->value = isset($item['value'])?$item['value']:$faker->text(10);

                   			$model->state_id = self::STATE_ACTIVE;

                    

                    	$model->type_id = isset($item['type_id'])?$item['type_id']:0;

                   
        	$model->save();

        }

	}	

}

