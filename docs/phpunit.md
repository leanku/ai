## 单元测试说明

```bash
# 1.安装测试依赖
composer require --dev phpunit/phpunit mockery/mockery

# 运行测试
# 运行所有测试
./vendor/bin/phpunit

# 运行特定测试文件
./vendor/bin/phpunit tests/Support/ConfigTest.php

# 运行测试并生成覆盖率报告
./vendor/bin/phpunit --coverage-html coverage
```

在 composer.json 中添加测试脚本后，可以使用```composer test```命令测试
```json
{
    "scripts": {
        "test": "vendor/bin/phpunit",
        "test-coverage": "vendor/bin/phpunit --coverage-html coverage"
    }
}
```