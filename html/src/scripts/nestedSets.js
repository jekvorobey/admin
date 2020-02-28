export default class NestedSets {
    static process(arr) {

        // перегоняем массив в объект, чтобы легко брать элементы по их лефтам
        let obj = {};
        arr.map(item => obj[item._lft] = item);

        let result = {children: []};
        processLevel(result, 1);

        // функция ищет всех чайлдов и помещает их в массив children объекта parentObject
        // _lft - лефт первого чайлда
        function processLevel(parentObject, _lft) {
            let item = obj[_lft];

            // если чайлд существует
            while (item) {
                // создаём для него дочерний объект с пустым массивом для возможных чайлдов этого чайлда
                let childObject = {id: item.id, name: item.name, children: []};

                // кладём его к братьям
                parentObject.children.push(childObject);

                // рекурсивно вызываем эту же функцию, чтобы найти всех чайлдов этого чайлда
                // лефт первого чайлда равен лефту родителя плюс один
                processLevel(childObject, item._lft + 1);

                // лефт каждого следующего чайлда равен райту предыдущего плюс один
                item = obj[item._rgt + 1];
            }
        }

        return result;
    }
}