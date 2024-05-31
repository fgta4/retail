import { fgta4slideselect } from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'
import { fgta4ParallelProcess } from '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4parallel.mjs'


let grd_list, opt;
var this_page_id;
var this_page_options;


const cbo_search_brand = $('#pnl_list-cbo_search_brand');


export function init(param, fn_callback) {
	grd_list = param.grd_list;
	opt = param.opt;
	this_page_id = opt.id;
	this_page_options = opt;	


	grd_list.autoload = false;


	var parallelProcess = fgta4ParallelProcess({
		waitfor: {
			cbo_search_brand_created: 1
		},
		onFinished: () => {
			grd_list.doLoad();
		}
	})



	cbo_search_brand.name = 'pnl_list-cbo_search_brand'	
	new fgta4slideselect(cbo_search_brand, {
		title: 'Pilih Brand',
		returnpage: this_page_id,
		api: $ui.apis.load_brand_id,

		fieldValue: 'brand_id',
		fieldValueMap: 'brand_id',
		fieldDisplay: 'brand_name',
		fields: [
			{ mapping: 'brand_name', text: 'Brand' },
		],
		OnDataLoading: (criteria) => {
			// console.log('loading...');
			criteria.brand_isdisabled=0;
		},
		OnDataLoaded: (result, options) => {
			result.records.unshift({ brand_id: 'ALL', brand_name: 'ALL' });
		},
		OnSelected: (value, display, record, options) => {
			// console.log(record);
			options.flashhighlight = false
			grd_list.doLoad();
		},
		OnCreated: () => {
			cbo_search_brand.combo('setValue', 'ALL');
			cbo_search_brand.combo('setText', 'ALL');
			parallelProcess.setFinished('cbo_search_brand_created');
		}
	});
	
	fn_callback();
}



export function customsearch(options) {
	var brand_id = cbo_search_brand.combo('getValue');
	if (brand_id!='ALL') {
		options.criteria.brand_id = brand_id;
	}

}