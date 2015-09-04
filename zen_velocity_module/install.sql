// for new installation
create table if not exists velocity_transactions (
id int not null auto_increment, 
transaction_id varchar(220), 
transaction_status varchar(100) not null, 
order_id varchar(10) not null, 
request_obj text not null,
response_obj text not null, 
primary key(id)
);

//for upgrade the table. 
alter table velocity_transactions  add request_obj text;