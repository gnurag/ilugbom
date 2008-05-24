class Person < ActiveRecord::Base
  has_many :articles, :foreign_key => "author_id"
  has_many :minutes, :foreign_key => "author_id"
  has_many :pages, :foreign_key => "author_id"
end
