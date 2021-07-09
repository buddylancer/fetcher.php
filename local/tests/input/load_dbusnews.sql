set names 'utf8';

delete from sources;
/* DO NOT change IDs after initial load */
set @site = 'http://www.ff.com:8000/Testing/feed[#File_Ext]';
insert into sources values (10, 'education.usnews.com', 1, 1,   'http://www.usnews.com/education',  concat(@site, '?source=education.usnews.com'));
insert into sources values (20, 'health.usnews.com',    1, 1,   'http://health.usnews.com',         concat(@site, '?source=health.usnews.com'));
insert into sources values (30, 'money.usnews.com',     1, 1,   'http://money.usnews.com',          concat(@site, '?source=money.usnews.com'));
insert into sources values (40, 'news.usnews.com',      1, 1,   'http://www.usnews.com/news',       concat(@site, '?source=news.usnews.com'));
insert into sources values (50, 'opinion.usnews.com',   1, 1,   'http://www.usnews.com/opinion',    concat(@site, '?source=opinion.usnews.com'));
insert into sources values (60, 'travel.usnews.com',    1, 1,   'http://travel.usnews.com',         concat(@site, '?source=travel.usnews.com'));

delete from categories;
/*insert into categories values ('US', 'United States', '\\bUS.| US;| US,|U.S.|USA|U.S.A.|United States', 0);*/
insert into categories values ('AL', 'Alabama', 'Alabama', 0);
insert into categories values ('AK', 'Alaska', 'Alaska', 0);
insert into categories values ('AZ', 'Arizona', 'Arizona|Phoenix', 0);
insert into categories values ('AR', 'Arkansas', 'Arkansas', 0);
insert into categories values ('CA', 'California', 'California|Santa Barbara|San Francisco|Los Angeles|Los Ángeles', 0);
insert into categories values ('CO', 'Colorado', 'Colorado|Denver', 0);
insert into categories values ('CT', 'Connecticut', 'Connecticut', 0);
insert into categories values ('DE', 'Delaware', 'Delaware', 0);
insert into categories values ('FL', 'Florida', 'Florida|Miami', 0);
insert into categories values ('GA', 'Georgia', 'Georgia|Atlanta', 0);
insert into categories values ('HI', 'Hawaii', 'Hawaii|Honolulu', 0);
insert into categories values ('ID', 'Idaho', 'Idaho', 0);
insert into categories values ('IL', 'Illinois', 'Illinois|Chicago', 0);
insert into categories values ('IN', 'Indiana', 'Indiana|Indianapolis', 0);
insert into categories values ('IA', 'Iowa', 'Iowa|Des Moines', 0);
insert into categories values ('KS', 'Kansas', 'Kansas|Topeka~Arkansas', 0);
insert into categories values ('KY', 'Kentucky', 'Kentucky|Frankfort', 0);
insert into categories values ('LA', 'Louisiana', 'Louisiana|New Orlean|Nueva Orleans|Baton Rouge', 0);
insert into categories values ('ME', 'Maine', 'Maine|Augusta', 0);
insert into categories values ('MD', 'Maryland', 'Maryland|Annapolis', 0);
insert into categories values ('MA', 'Massachusetts', 'Massachusetts|Boston', 0);
insert into categories values ('MI', 'Michigan', 'Michigan|Lansing', 0);
insert into categories values ('MN', 'Minnesota', 'Minnesota|Saint Paul', 0);
insert into categories values ('MS', 'Mississippi', 'Mississippi|Jackson City', 0);
insert into categories values ('MO', 'Missouri', 'Missouri|Jefferson City', 0);
insert into categories values ('MT', 'Montana', 'Montana|Helena City', 0);
insert into categories values ('NE', 'Nebraska', 'Nebraska|Lincoln City', 0);
insert into categories values ('NV', 'Nevada', 'Nevada|Carson City', 0);
insert into categories values ('NH', 'New Hampshire', 'New Hampshire|Concord', 0);
insert into categories values ('NJ', 'New Jersey', 'New Jersey|Trenton', 0);
insert into categories values ('NM', 'New Mexico', 'New Mexico|Santa Fe', 0);
insert into categories values ('NY', 'New York', 'New York|Albany', 0);
insert into categories values ('NC', 'North Carolina', 'North Carolina|Raleigh', 0);
insert into categories values ('ND', 'North Dakota', 'North Dakota|Bismarck', 0);
insert into categories values ('OH', 'Ohio', 'Ohio|Columbus', 0);
insert into categories values ('OK', 'Oklahoma', 'Oklahoma', 0);
insert into categories values ('OR', 'Oregon', 'Oregon|Salem~Jerusalem|Winston-Salem|Massachusetts', 0);
insert into categories values ('PA', 'Pennsylvania', 'Pennsylvania|Harrisburg', 0);
insert into categories values ('RI', 'Rhode Island', 'Rhode Island|Providence', 0);
insert into categories values ('SC', 'South Carolina', 'South Carolina|Columbia', 0);
insert into categories values ('SD', 'South Dakota', 'South Dakota|Pierre', 0);
insert into categories values ('TN', 'Tennessee', 'Tennessee|Nashville', 0);
insert into categories values ('TX', 'Texas', 'Texas|Austin', 0);
insert into categories values ('UT', 'Utah', 'Utah|Salt Lake City', 0);
insert into categories values ('VT', 'Vermont', 'Vermont|Montpelier', 0);
insert into categories values ('VA', 'Virginia', 'Virginia|Richmond', 0);
insert into categories values ('WA', 'Washington', 'Washington|Olympia', 0);
insert into categories values ('WV', 'West Virginia', 'West Virginia|Charleston', 0);
insert into categories values ('WI', 'Wisconsin', 'Wisconsin|Madison', 0);
insert into categories values ('WY', 'Wyoming', 'Wyoming|Cheyenne', 0);
/* --- */
insert into categories values ('AS', 'American Samoa', 'American Samoa|Pago Pago', 0);
insert into categories values ('DC', 'District of Columbia', 'D\\.C\\.|District of Columbia', 0);
insert into categories values ('FM', 'Federated States of Micronesia', 'Federated States of Micronesia', 0);
insert into categories values ('GU', 'Guam', 'Guam|Hagåtña', 0);
insert into categories values ('MH', 'Marshall Islands', 'Marshall Islands', 0);
insert into categories values ('MP', 'Northern Mariana Islands', 'Northern Mariana Islands|Saipan', 0);
insert into categories values ('PW', 'Palau', 'Palau', 0);
insert into categories values ('PR', 'Puerto Rico', 'Puerto Rico|San Juan', 0);
insert into categories values ('VI', 'Virgin Islands', 'Virgin Islands|Charlotte Amalie', 0);
/* --- */
insert into categories values ('Britain', 'World - Britain', 'Britain|Britanian|British|London', 0);
insert into categories values ('Canada', 'World - Canada', 'Canada|Vancouver|Ottawa|Vinnipeg|Toronto', 0);
insert into categories values ('China', 'World - China', 'China|Chinese', 0);
insert into categories values ('Czechia', 'World - Czech Republic', 'Czech Republic|Prague|Brno', 0);
insert into categories values ('France', 'World - France', 'France|French|Paris', 0);
insert into categories values ('Germany', 'World - Germany', 'Germany|German|Berlin', 0);
insert into categories values ('Iran', 'World - Iran', 'Iran|Iranian', 0);
insert into categories values ('Mexico', 'World - Mexico', 'Mexico~New Mexico', 0);
insert into categories values ('Netherlands', 'World - Netherlands', 'Netherlands|Amsterdam', 0);
insert into categories values ('Poland', 'World - Poland', 'Poland|Polish|Warshawa', 0);
insert into categories values ('Russia', 'World - Russia', 'Russia|Moscow', 0);
insert into categories values ('South Korea', 'World - South Korea', 'South Korea', 0);
insert into categories values ('Scotland', 'World - Scotland', 'Scotland', 0);

delete from mappings;
insert into mappings values (0, '&amp;', '&');
insert into mappings values (0, '&lt;', '<');
insert into mappings values (0, '&gt;', '>');
insert into mappings values (0, '&laquo;', '«');
insert into mappings values (0, '&raquo;', '»');
insert into mappings values (0, '&#171;', '«');
insert into mappings values (0, '&#187;', '»');
insert into mappings values (0, '&#8364;', '€');
insert into mappings values (0, '&#10625;', '-'); /* dot */
insert into mappings values (0, '&#34;', '"');
insert into mappings values (0, '&#39;', '\'');
insert into mappings values (0, '&#8722;', '-'); /* dash */

delete from rules;
insert into rules values (0, '*', 'title', 'shrink', NULL, 0, 'http', NULL); /* shrink the end */
insert into rules values (0, '*', 'description', 'shrink', NULL, 0, 'http', NULL); /* shrink the end */
insert into rules values (0, '*', 'title', 'truncate', NULL, 140, NULL, NULL);
insert into rules values (0, '*', 'description', 'truncate', NULL, 500, NULL, NULL);

