class Article < ActiveRecord::Base
  belongs_to :author, :foreign_key => "author_id", :class_name => "Person"
end
