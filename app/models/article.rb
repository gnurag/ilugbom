class Article < ActiveRecord::Base
  belongs_to :author, :foreign_key => "author_id", :class_name => "Person"
  validates_presence_of :title, :body, :author_id, :created_at
end
