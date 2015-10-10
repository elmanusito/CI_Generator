# CI_Generator
CI_Generator is a simple PHP class that help you to create a simple structure of models and controllers for Codeigniter

### Installation
Go to /[your-project]/application/third_party/ and clone the repo:
```sh
$ git clone [https://github.com/elmanusito/CI_Generator] CI_Generator
```
Then, go to your controllers folders and create the next controller:
```
<?php
class Tools extends CI_Controller {

		public function ci_generator()
		{
			require APPPATH . 'third_party/CI_Generator/ci_generator.php';
		}
}
```
Now, run your controller:
```
http://www.your-site.com/index.php/tools/ci_generator
```

Too, you can use your localhost to make test ;)
```
http://localhost/your-site/index.php/tools/ci_generator
```
Then, go to:
```sh
[your-project]/application/third_party/CI_Generator
```

And enjoy it!

### Version
1.0.0