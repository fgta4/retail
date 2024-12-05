'use strict'

const dbtype = global.dbtype;
const comp = global.comp;

module.exports = {
	title: "Merchandise",
	autoid: true,

	
	persistent: {
		'fsn_mercharticle' : {
			primarykeys: ['mercharticle_id'],
			comment: 'Daftar article merchandise',
			data: {
				mercharticle_id: { 
					text: 'ID', type: dbtype.varchar(14),  null: false,  options: { required: true, invalidMessage: 'ID harus diisi' } 
				},
				
				mercharticle_art: {text:'ART', type: dbtype.varchar(30), null:true, suppresslist: true, options:{required:true,invalidMessage:'Artikel harus diisi'}},
				mercharticle_mat: {text:'MAT', type: dbtype.varchar(30), null:true, suppresslist: true},
				mercharticle_col: {text:'COL', type: dbtype.varchar(30), null:true, suppresslist: true},
				
				mercharticle_name: {text:'Name', type: dbtype.varchar(255), null:true},
				mercharticle_descr: {text:'Descr', type: dbtype.varchar(2500), null:true, suppresslist: true},

				mercharticle_isdisabled: { text: 'Disabled', type: dbtype.boolean, null: false, default: '0' },

				mercharticle_pcpline: {text:'pcp Line', type: dbtype.varchar(90), null:true, suppresslist: true},
				mercharticle_pcpgroup: {text:'pcp group', type: dbtype.varchar(90), null:true, suppresslist: true},
				mercharticle_pcpcategory: {text:'pcp category', type: dbtype.varchar(90), null:true, suppresslist: true},
				mercharticle_gender: {text:'Gender', type: dbtype.varchar(1), null:true, suppresslist: true},
				mercharticle_fit: {text:'Fit', type: dbtype.varchar(30), null:true, suppresslist: true},
				mercharticle_hscodeship: {text:'HSCode (ship)', type: dbtype.varchar(30), null:true, suppresslist: true},
				mercharticle_hscodeina: {text:'HSCode (ina)', type: dbtype.varchar(30), null:true, suppresslist: true},
				mercharticle_gtype: {text:'GType', type: dbtype.varchar(5), null:true, suppresslist: true},
				mercharticle_labelname: {text:'label name', type: dbtype.varchar(90), null:true, suppresslist: true},
				mercharticle_labelproduct: {text:'label product', type: dbtype.varchar(90), null:true, suppresslist: true},
				mercharticle_bahan: {text:'label bahan', type: dbtype.varchar(150), null:true, suppresslist: true},
				mercharticle_pemeliharaan: {text:'label pemeliharaan', type: dbtype.varchar(150), null:true, suppresslist: true},
				mercharticle_logo: {text:'label logo', type: dbtype.varchar(30), null:true, suppresslist: true},
				mercharticle_dibuatdi: {text:'label dibuatdi', type: dbtype.varchar(30), null:true, suppresslist: true},

				merchctg_id: { 
					text: 'Category', type: dbtype.varchar(30), null: false, suppresslist: true,
					reference: {table: 'fsn_merchctg', field_value: 'merchctg_id', field_display:'merchctg_name',  field_display_name:'merchctg_name'}, 
					options: { required: true, invalidMessage: 'Category harus diisi' }, 
				},

				merchsea_id: { 
					text: 'Season', type: dbtype.varchar(10), null: false, suppresslist: true,
					reference: {table: 'fsn_merchsea', field_value: 'merchsea_id', field_display:'merchsea_name',  field_display_name:'merchsea_name'}, 
					options: { required: true, invalidMessage: 'Season harus diisi' }, 
				},

				unit_id: { 
					text: 'Unit', type: dbtype.varchar(10), null: false, suppresslist: true,
					reference: {table: 'mst_unit', field_value: 'unit_id', field_display:'unit_name',  field_display_name:'unit_name'}, 
					options: { required: true, invalidMessage: 'Unit harus diisi' }, 
				},

				brand_id: { 
					text: 'Brand', type: dbtype.varchar(14), null: false, suppresslist: true,
					reference: {table: 'mst_brand', field_value: 'brand_id', field_display:'brand_name',  field_display_name:'brand_name'}, 
					options: { required: true, invalidMessage: 'Brand harus diisi' }, 
				},

				dept_id: { 
					text: 'Dept', type: dbtype.varchar(30), null: false, suppresslist: true,
					reference: {table: 'mst_dept', field_value: 'dept_id', field_display:'dept_name',  field_display_name:'dept_name'}, 
					options: { required: true, invalidMessage: 'Dept harus diisi' }, 
				}
			},

			uniques : {
				mercharticle_uniq: ['dept_id', 'mercharticle_art', 'mercharticle_mat', 'mercharticle_col'],
			}
		},

		'fsn_merchitem' : {
			primarykeys: ['merchitem_id'],
			comment: 'Daftar Item merchandise',
			data: {
				merchitem_id: { 
					text: 'ID', type: dbtype.varchar(14),  null: false, 
					reference: {table: 'mst_itemstock', field_value: 'itemstock_id', field_display:'itemstock_name',  field_display_name:'itemstock_name'}, 
					options: { required: true, invalidMessage: 'ID harus diisi' } 
				},
				merchitem_size: {text:'SIZE', type: dbtype.varchar(20), null:true, suppresslist: true},
				merchitem_isdisabled: { text: 'Disabled', type: dbtype.boolean, null: false, default: '0' },

				unit_id: { 
					text: 'Unit', type: dbtype.varchar(10), null: false, suppresslist: true,
					reference: {table: 'mst_unit', field_value: 'unit_id', field_display:'unit_name',  field_display_name:'unit_name'}, 
					options: { required: true, invalidMessage: 'Unit harus diisi' }, 
				},

				brand_id: { 
					text: 'Brand', type: dbtype.varchar(14), null: false, suppresslist: true,
					reference: {table: 'mst_brand', field_value: 'brand_id', field_display:'brand_name',  field_display_name:'brand_name'}, 
					options: { required: true, invalidMessage: 'Brand harus diisi' }, 
				},

				dept_id: { 
					text: 'Dept', type: dbtype.varchar(30), null: false, suppresslist: true,
					reference: {table: 'mst_dept', field_value: 'dept_id', field_display:'dept_name',  field_display_name:'dept_name'}, 
					options: { required: true, invalidMessage: 'Dept harus diisi' }, 
				},

				mercharticle_id: { text: 'ID', type: dbtype.varchar(14),  null: false  },

			},

			uniques : {
				merchitem_uniq: ['mercharticle_id', 'merchitem_size'],
			}
		},
	},

	schema: {
		title: 'Merchandise Articles',
		header: 'fsn_mercharticle',
		detils: {
			'item': {
				title: 'Items', table: 'fsn_merchitem', form: true, headerview: 'merchitem_descr', 
				editorHandler: true, listHandler: true 
			},
		}
	}

}