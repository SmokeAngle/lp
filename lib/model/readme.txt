model层分数据层和业务层，多数情况下，对数据的原子操作都可以放在数据层(dyact3.niu.xunlei.com/model/)，如果业务需要把逻辑抽离出来，可以放在这个目录下。
注意：避免业务层model跟数据层model功能上重合，也就是业务层model干了数据层model的事，要求业务层model必须继承数据层model，业务层model不能直接继承model基类, 否则，容易混淆。