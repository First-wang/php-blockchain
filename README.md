# php-blockchain

[![](https://img.shields.io/badge/language-php-green)](https://www.php.net/)

基于 [LaravelZero](https://laravel-zero.com/) 简单构建了一个区块链

## 前言

本项目根据 [blockchain_go](https://github.com/Jeiwan/blockchain_go) 使用PHP实现。
完成了区块链的一些关键特性，包括钱包、地址、工作量证明系统、交易、UTXO，但不包括P2P网络，交易内存池等等，并且提供了CLI交互命令。

实现细节：

- [基本原型](https://learnku.com/articles/43913)
- [工作量证明](https://learnku.com/articles/43914)
- [数据持久化与CLI](https://learnku.com/articles/43917)
- [交易1](https://learnku.com/articles/43919)
- [钱包、地址与密钥](https://learnku.com/articles/43954)
- 交易2

## 安装

相关环境参考 LaravelZero

```shell script
git clone https://github.com/First-wang/php-blockchain.git

cd php-blockchain

composer install 
```


## 用法

获取所有命令及用法
```shell script
php blockchain 
```

## 参考资料

[Going the distance](https://jeiwan.net/)

[blockchain_go](https://github.com/Jeiwan/blockchain_go)

[blockchain-tutorial](https://github.com/liuchengxu/blockchain-tutorial)

## 最后

由于水平有限，如有错误之处，欢迎指出。

- BTC：  1KGP4Pu6QuZ12X67KmMReG13cjx56d6LwT
