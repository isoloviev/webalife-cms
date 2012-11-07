Ext.grid.CheckboxColumn = function(config){

    config.id = config.id || 'ck';

    config.columnId = config.id || 'ck';

    return Ext.applyIf(config||{},{

        init:function(grid){

            grid.on('cellclick', this.onCellClick, this);

            grid.on('headerclick',this.onHeaderClick,this);

            }

        ,dataIndex:''

        ,header:'<div class="x-grid3-check-col"></div>'

        ,enableHeaderControl:true

        ,masterValue:false

        ,width:45

        ,fixed:true

        ,headerUnchecked:'<div class="x-grid3-check-col"></div>'

        ,headerChecked:'<div class="x-grid3-check-col x-grid3-check-col-on"></div>'

        ,onHeaderClick:function(grid,columnIndex,event){

            var cIndex = grid.getColumnModel().getIndexById(this.columnId);

            var column = grid.getColumnModel().getColumnById(this.columnId);

            if(cIndex == columnIndex && this.enableHeaderControl!==false){

                var newValue = (typeof column.masterValue == "undefined")?this.masterValue:!column.masterValue;

                column.masterValue = newValue; 

                var newHeader = newValue==true?this.headerChecked:this.headerUnchecked;

                if(column.header != newHeader){

                    column.header = newValue==true?this.headerChecked:this.headerUnchecked;

                    grid.getColumnModel().fireEvent("headerchange",cIndex,newHeader);

                }

                grid.getView().updateHeaders();

                if(this.dataIndex != ''){

                    var ct = grid.getStore().getCount();

                    for(var i=0;i<ct;i++){

                        this.toggleCheck(grid,i,columnIndex,newValue);

                    }

                }

            }

        }

        ,onCellClick:function(grid,rowIndex,columnIndex,event){

            var cIndex = grid.getColumnModel().getIndexById(this.columnId);

            if(cIndex == columnIndex) this.toggleCheck(grid,rowIndex,columnIndex);

        }

        ,toggleCheck:function(grid,rowIndex,columnIndex,newValue){

                var td = grid.getView().getCell(rowIndex,columnIndex);

                var record = grid.getStore().getAt(rowIndex);

                var startValue = record.data[this.dataIndex];

                if(this.dataIndex != ''){

                    var newValue = newValue||!Ext.fly(td).hasClass('x-grid3-check-col-on');

                    var e = {
                        grid: grid,
                        record: record,
                        field: this.dataIndex,
                        value: newValue,
                        originalValue: startValue,
                        row: rowIndex,
                        column: columnIndex,
                        cancel: false
                    };

                    if(    (grid.fireEvent("beforeedit",e)!==false && !e.cancel)&& 

                        (grid.fireEvent("validateedit",e)!==false && !e.cancel)){
                            record.set(this.dataIndex,newValue);
                            delete e.cancel;
                            grid.fireEvent("afteredit",e);

                    }

                }            

        }

        ,renderer:function(value,meta,record){
            meta.css = 'x-grid3-check-col-td x-grid3-check-col';
            if(value==true || value=='true' || value=='on' || value==1 || value=='1') meta.css += ' x-grid3-check-col-on';
            return '<div class="x-grid3-check-col-inner"> </div>';
        }

    });

} 



