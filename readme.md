# Базовая модель

Предназначена для изоляции бизнес логики от фреймворка. 
Для подключения к конкретному фреймворку, необходим класс расширяющий EntityManager 

Бизнес-логика описывается как в сущностях (классах наследующих класс Entity),
так и в классах наследующих класс Aggregate, который оперирует сущностями. 
Любые манипуляции с сущностями желательно производить через агрегаты.

Методы класса Aggregate в качестве аргументов принимают некоторые параметры скалярного типа, 
либо иммутабельные объекты наследующие ValueObject. 
В них происходит валидация и фильтрация входных данных
В качесве параметров нельзя передавать ссылки на объекты фреймворка.

Пример связи с фреймворком Yii:
```php 
new YiiEntityManager(); // Наследуется от AbstractEntityManager

$profile = new Profile(); // Наследуется от Aggregate
try{
	$userInfoData = new UserData($login, $password); // Наследуется от ValueObject
	$profile->updateUserInfo($userId, $userInfoData); // Меняет состояние сущности UserAttributes
}catch(ValidateException $e){
	echo 'Ошибка валидации: ' . $e->getMessage();
}
```

Инициализация встроенных компонент:

```php
// Инициализация карты преобразователей
$mappersMap = new \BaseModel\MappersMap();

// Регистрация адаптера или любых других сервисов для преобразователей
$mappersMap['adapter'] = function (){
    $dsn= 'mysql:host=localhost;dbname=test;charset=utf8';
    $username = 'test';
    $password = 'secret';
    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    return new \BaseModel\Adapter($pdo);
};

// Связь преобразователя с сущностью
$mappersMap->bindEntityWithMapper(
    Product::class, 
    \BaseModel\Mapper::lazyBuild('products', Product::class)
);

// Инициализация менеджера сущностей
new \BaseModel\EntityManager($mappersMap);

// Инициализация агрегата
$catalog = new Catalog(); // Наследуется от Aggregate

try{
	$productData = new ProductData($title, $description); // Наследуется от ValueObject
	$catalog->updateProduct($productId, $productData);  // Меняет состояние сущности Product
}catch(ValidateException $e){
	echo 'Ошибка валидации: ' . $e->getMessage();
}
```

На модель или сущности можно "повесить" наблюдателя. 
Состояние субъекта наблюдения определяется через флаг состояния. 
В качестве примера реализован логгер. Это необходимо для получения состояния модели 
без вмешательства в код модели.

Связь между сущностями осуществляется через EntityManager. 
Пример:

```php 

class User extends Entity
{
	public function getAvatar()
	{
		return $this->entityManager->findOne(Avatar::class, ['userId' => $this->getId()]);
	}
}
```

Такие методы являются lazy load и после первого обращения в сущности сохраняется ссылка на объект.
