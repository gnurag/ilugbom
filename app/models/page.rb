class Page < ActiveRecord::Base
  belongs_to :author, :foreign_key => "author_id", :class_name => "Person"
  
  validates_presence_of :author_id
  validates_size_of     :title, :within => 1..150
  validates_length_of   :body, :maximum => 10240, :message => "too long. Max length is 10KB"
end
