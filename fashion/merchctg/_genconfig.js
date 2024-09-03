'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Category",
	autoid: false,

	persistent: {
		'fsn_merchctg' : {
			primarykeys: ['merchctg_id'],
			comment: 'Daftar Category Item Fashion Merchandise',
			data: {
				merchctg_id: {text:'ID', type: dbtype.varchar(30), null:false, uppercase: true, options:{required:true,invalidMessage:'ID harus diisi'}},
				merchctg_name: {text:'Category Name', type: dbtype.varchar(90), null:false, uppercase: true, options:{required:true,invalidMessage:'Nama Kategori harus diisi'}},
				merchctg_nameshort: {text:'Name Short', type: dbtype.varchar(90)},
				merchctg_descr: {text:'Description', type: dbtype.varchar(255), null:false},

				gender_id: { 
					text: 'Gender', type: dbtype.varchar(7), null: true, suppresslist: true,
					options: { prompt: 'NONE' }, 
					comp: comp.Combo({
						table: 'mst_gender',
						field_value: 'gender_id', field_display: 'gender_name',
						api: 'ent/general/gender/list'
					})
				},

				dept_id: { 
					text: 'Dept Owner', type: dbtype.varchar(10), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'Dept harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_dept',
						field_value: 'dept_id', field_display: 'dept_name',
						api: 'ent/organisation/dept/list-byuser',
						onDataLoadingHandler: true,
						onDataLoadedHandler: false,
						onSelectingHandler: false,
						onSelectedHandler: true
					})
				},

				itemgroup_id: { 
					text: 'Item Group', type: dbtype.varchar(17), suppresslist: true,
					options: { required: true, invalidMessage: 'Item Group harus diisi' }, 
					comp: comp.Combo({
						table: 'mst_itemgroup',
						field_value: 'itemgroup_id', field_display: 'itemgroup_name',
						field_mappings: [
							`{mapping: 'itemgroup_id', text: 'ID', hidden: true, style: 'width: 100px'}`,
							`{mapping: 'itemgroup_name', text: 'Item Group', style: 'width: auto; padding-left: 10px'}`,
							`{mapping: '_id', text: 'ID', style: 'width: 100px'}`,
						],
						api: 'ent/items/itemgroup/list-bydept',
						onDataLoadingHandler: true,
						onDataLoadedHandler: false,
						onSelectingHandler: true,
						onSelectedHandler: false
					})
				},

				unit_id: { 
					text: 'Unit', type: dbtype.varchar(10), null: false, suppresslist: true,
					options: { required: true, invalidMessage: 'Unit harus diisi', disabled:true }, 
					comp: comp.Combo({
						table: 'mst_unit',
						field_value: 'unit_id', field_display: 'unit_name',
						api: 'ent/organisation/unit/list',
						onDataLoadingHandler: false,
						onDataLoadedHandler: false,
						onSelectingHandler: false,
						onSelectedHandler: false
					})
				},


			},
			defaultsearch : ['merchctg_id', 'merchctg_name'],
			uniques: {
				'merchctg_name' : ['unit_id', 'merchctg_name']
			}
		},

	},


	schema: {
		title: 'Category',
		header: 'fsn_merchctg',
		detils: {
		}
	}


}
